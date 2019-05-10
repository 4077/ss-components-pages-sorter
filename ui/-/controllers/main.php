<?php namespace ss\components\pagesSorter\ui\controllers;

class Main extends \Controller
{
    public function apply()
    {
        $pivot = $this->data('pivot');
        $cat = $pivot->cat;

        $groupId = ss()->cats->getSelectedWarehousesGroup($cat->tree_id);

        $order = aread($this->_protected('data', '^app~:cat_' . $cat->id . '/orders/group_' . $groupId . '.php'));

        $this->c('\ss\components\cats svc:setOrder', [
            'cat'   => $cat,
            'order' => $order
        ]);
    }
}
