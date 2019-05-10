<?php namespace ss\components\pagesSorter\app\controllers;

class Main extends \Controller
{
    /**
     * @var \ewma\Process\AppProcess
     */
    private $process;

    private $groups = [];

    private $pivot;

    private $pivotData;

    private $cat;

    public function __create()
    {
        $this->process = process();

        $this->pivot = $this->unpackModel('pivot');
        $this->cat = $this->pivot->cat;

        $this->pivotData = _j($this->pivot->data);

        $this->groups = ss()->multisource->getWarehousesGroups();
    }

    public function pivotData($path = false)
    {
        return ap($this->pivotData, $path);
    }

    public function renderOrders()
    {
        $idsByPosition = table_ids($this->cat->containedPages()->orderBy('position')->get());

        $byGroupEnabled = $this->pivotData('by_group/enabled');
        $byGroupThreshold = $this->pivotData('by_group/threshold');

        $byOthersEnabled = $this->pivotData('by_others/enabled');
        $byOthersThreshold = $this->pivotData('by_others/threshold');

        foreach ($this->groups as $group) {
            $groupId = $group->id;

            $groupWeights = aread($this->_protected('data', 'cat_' . $this->cat->id . '/weights/group_' . $groupId . '/group.php')) ?? [];
            $othersWeights = aread($this->_protected('data', 'cat_' . $this->cat->id . '/weights/group_' . $groupId . '/others.php')) ?? [];

            $groupWeights = array_filter($groupWeights, function ($value) use ($byGroupThreshold) {
                return $value > $byGroupThreshold;
            });

            $othersWeights = array_filter($othersWeights, function ($value) use ($byOthersThreshold) {
                return $value > $byOthersThreshold;
            });

            arsort($groupWeights);
            arsort($othersWeights);

            $sorted = [];
            $order = [];

            if ($byGroupEnabled && $byOthersEnabled) {
                foreach ($groupWeights as $productId => $weight) {
                    $sorted[] = $productId;
                    $order[] = $productId;
                }

                foreach ($othersWeights as $productId => $weight) {
                    if (!in_array($productId, $sorted)) {
                        $sorted[] = $productId;
                        $order[] = $productId;
                    }
                }

                foreach ($idsByPosition as $productId) {
                    if (!in_array($productId, $sorted)) {
                        $order[] = $productId;
                    }
                }
            }

            if ($byGroupEnabled && !$byOthersEnabled) {
                foreach ($groupWeights as $productId => $weight) {
                    $sorted[] = $productId;
                    $order[] = $productId;
                }

                foreach ($idsByPosition as $productId) {
                    if (!in_array($productId, $sorted)) {
                        $order[] = $productId;
                    }
                }
            }

            if (!$byGroupEnabled && $byOthersEnabled) {
                foreach ($othersWeights as $productId => $weight) {
                    $sorted[] = $productId;
                    $order[] = $productId;
                }

                foreach ($idsByPosition as $productId) {
                    if (!in_array($productId, $sorted)) {
                        $order[] = $productId;
                    }
                }
            }

            if (!$byGroupEnabled && !$byOthersEnabled) {
                $order[] = $idsByPosition;
            }

            awrite($this->_protected('data', 'cat_' . $this->cat->id . '/orders/group_' . $groupId . '.php'), $order);
        }
    }
}
