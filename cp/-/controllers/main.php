<?php namespace ss\components\pagesSorter\cp\controllers;

class Main extends \Controller
{
    private $pivot;

    private $pivotXPack;

    private $pivotData;

    public function __create()
    {
        if ($this->pivot = $this->unpackModel('pivot')) {
            $this->instance_($this->pivot->cat_id);

            $this->pivotXPack = xpack_model($this->pivot);
            $this->pivotData = _j($this->pivot->data);
        } else {
            $this->lock();
        }
    }

    public function pivotData($path = false)
    {
        return ap($this->pivotData, $path);
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $pivot = $this->pivot;

        $v->assign([
                       'BY_GROUP_TOGGLE' => $this->toggleButton('by_group/enabled'),
                       'BY_OTHERS_TOGGLE'   => $this->toggleButton('by_others/enabled'),
                   ]);

        if ($this->pivotData('by_group/enabled')) {
            $v->assign('selected_enabled', [
                'INPUT' => $this->stringValueInput('by_group/threshold')
            ]);
        }

        if ($this->pivotData('by_others/enabled')) {
            $v->assign('others_enabled',[
                'INPUT' => $this->stringValueInput('by_others/threshold')
            ]);
        }

        $this->css();

        $renderXPid = false;
        if ($renderPid = $this->d('^app~:pids/render|')) {
            if ($process = $this->app->processDispatcher->open($renderPid)) {
                $renderXPid = $process->getXPid();
            } else {
                $this->d('^app~:pids/render|', false, RR);
            }
        }

        $this->widget(':|', [
            '.payload'   => [
                'pivot' => $this->pivotXPack
            ],
            '.r'         => [
                'updateStringValue' => $this->_p('>xhr:updateStringValue'),
                'render'            => $this->_p('>xhr:render|'),
                'reload'            => $this->_p('>xhr:reload|')
            ],
            'channelId'  => $pivot->id,
            'catId'      => $pivot->cat_id,
            'renderXPid' => $renderXPid
        ]);

        return $v;
    }

    private function toggleButton($path, $labels = ['да', 'нет'], $class = 'toggle')
    {
        $value = $this->pivotData($path);

        $buttonData = [
            'path'    => '>xhr:toggle',
            'data'    => [
                'pivot' => $this->pivotXPack,
                'path'  => j64_($path)
            ],
            'class'   => $class . ' ' . ($value ? 'enabled' : ''),
            'content' => $value ? $labels[0] : $labels[1]
        ];

        return $this->c('\std\ui button:view', $buttonData);
    }

    private function stringValueInput($path, $ra = [])
    {
        $attrs = [
            'path'  => j64_($path),
            'value' => $this->pivotData($path)
        ];

        if ($ra) {
            ra($attrs, $ra);
        }

        return $this->c('\std\ui tag:view:input', [
            'attrs' => $attrs
        ]);
    }
}
