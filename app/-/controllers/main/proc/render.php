<?php namespace ss\components\pagesSorter\app\controllers\main\proc;

class Render extends \Controller
{
    /**
     * @var \ewma\Process\AppProcess
     */
    private $process;

    private $groups = [];

    private $pivot;

    private $cat;

    public function __create()
    {
        $this->process = process();

        $this->pivot = $this->unpackModel('pivot');
        $this->cat = $this->pivot->cat;

        $this->groups = ss()->multisource->getWarehousesGroups();

        $this->instance_($this->cat->id);
    }

    public function run()
    {
        $this->buildDataTree();
        $this->renderGroupsWeights();
        $this->renderOrders();

        $this->d('^app~:pids/render|', false, RR);
    }

    private function renderOrders()
    {
        $this->c('~:renderOrders', [
            'pivot' => $this->pivot
        ]);
    }

    private function renderGroupsWeights()
    {
        $process = $this->process;

        $count = count($this->groups);
        $n = 0;

        $this->renderGroupWeights(0);
        $this->log('render ' . ++$n . '/' . $count . ' no group');
        $process->progress($n, $count);

        foreach ($this->groups as $group) {
            $this->renderGroupWeights($group->id);
            $this->log('render ' . ++$n . '/' . $count . ' ' . $group->name);
            $process->progress($n, $count);
        }
    }

    private function renderGroupWeights($groupId)
    {
        $process = $this->process;

        $otherGroupsIds = array_keys(unmap($this->groups, $groupId));

        $groupsStock[$groupId] = [];
        $otherGroupsStock[$groupId] = [];

        foreach ($this->pages as $pageId) {
            if (true === $process->handleIteration()) {
                break;
            }

            $groupsStock[$groupId][$pageId] = 0;
            $otherGroupsStock[$groupId][$pageId] = 0;

            $containers = $this->containers[$pageId] ?? [];

            foreach ($containers as $containerId) {
                $products = $this->products[$containerId] ?? [];

                foreach ($products as $productId) {
                    $productSummary = $this->productsSummary[$productId];

                    $groupSummary = $productSummary[$groupId];

                    $groupsStock[$groupId][$pageId] += $groupSummary->stock - $groupSummary->reserved;

                    foreach ($otherGroupsIds as $otherGroupId) {
                        $groupSummary = $productSummary[$otherGroupId];

                        $otherGroupsStock[$groupId][$pageId] += $groupSummary->stock - $groupSummary->reserved;
                    }
                }
            }
        }

        awrite($this->_protected('data', '~:cat_' . $this->cat->id . '/weights/group_' . $groupId . '/group.php'), $groupsStock[$groupId]);
        awrite($this->_protected('data', '~:cat_' . $this->cat->id . '/weights/group_' . $groupId . '/others.php'), $otherGroupsStock[$groupId]);
    }

    private $pages = [];

    private $containers = [];

    private $products = [];

    private $productsSummary = [];

    private function buildDataTree()
    {
        $process = process();

        $pivot = $this->unpackModel('pivot');
        $cat = $pivot->cat;

        $pages = $cat->containedPages()->with('containers')->orderBy('position')->get();

        $count = count($pages);
        $n = 0;

        foreach ($pages as $page) {
            if (true === $process->handleIteration()) {
                break;
            }

            $n++;

            $this->pages[] = $page->id;
            $this->containers[$page->id] = [];

            $containers = $page->containers()->orderBy('position')->get();

            foreach ($containers as $container) {
                $this->containers[$page->id][] = $container->id;

                $products = $container->products()->with('multisourceSummary')->orderBy('position')->get();

                foreach ($products as $product) {
                    $this->products[$container->id][] = $product->id;
                    $this->productsSummary[$product->id] = table_rows_by($product->multisourceSummary, 'warehouses_group_id');
                }
            }

            $this->log('loading ' . $n . '/' . $count . ' ' . $page->name);

            $process->progress($n, $count);
        }
    }
}
