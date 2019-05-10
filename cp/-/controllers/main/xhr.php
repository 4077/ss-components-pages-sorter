<?php namespace ss\components\pagesSorter\cp\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    private function triggerUpdate($catId, $reload = true)
    {
        pusher()->trigger('ss/container/' . $catId . '/update_pivot');

        if ($reload) {
            $this->reload();
        }
    }

    public function reload()
    {
        $this->c('~:reload', [], true);
    }

    public function updateStringValue()
    {
        if ($pivot = $this->unxpackModel('pivot')) {
            if ($path = _j64($this->data('path'))) {
                $value = $this->processStringValue($path);

                ss()->cats->apComponentPivotData($pivot, $path, $value);

                $this->valueUpdateCallback($pivot, $path);

                $this->widget('<:|', 'savedHighlight', $this->data('path'));

                $this->triggerUpdate($pivot->cat_id, false);
            }
        }
    }

    private function processStringValue($path)
    {
        $value = $this->data('value');

        if (in($path, 'by_group/threshold, by_others/threshold')) {
            $value = (int)$value;
        }

        return $value;
    }

    private function valueUpdateCallback($pivot, $path)
    {
        $this->c('^app~:renderOrders', [
            'pivot' => $pivot
        ]);
    }

    public function toggle()
    {
        if ($pivot = $this->unxpackModel('pivot')) {
            if ($path = _j64($this->data('path'))) {
                ss()->cats->invertComponentPivotData($pivot, $path);

                $this->valueUpdateCallback($pivot, $path);

                $this->triggerUpdate($pivot->cat_id);
            }
        }
    }

    //

    public function render()
    {
        if ($pivot = $this->unxpackModel('pivot')) {
            if ($this->data('sync')) {
                $this->c('^app~proc/render:run|', [
                    'pivot' => pack_model($pivot)
                ]);
            } else {
                $process = $this->proc('^app~proc/render:run|', [
                    'pivot' => pack_model($pivot)
                ])->pathLock()->run();

                if ($process) {
                    $this->d('^app~:pids/render|', $process->getPid(), RR);

                    pusher()->trigger('ss/components/pagesSorter/renderStart', [
                        'xpid' => $process->getXPid()
                    ]);

                    $this->app->response->json(['xpid' => $process->getXPid()]);
                }
            }
        }
    }
}
