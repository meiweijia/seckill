<?php

class Local
{
    /**
     * 本地扣减库存.
     * 
     * @param int $stock 本地库存
     * @param int $sales 本地已售
     * @return bool
     */
    public function deductStock(int $stock, int &$sales): bool
    {
        $sales++;
        return $sales <= $stock;
    }
}
