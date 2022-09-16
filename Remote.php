<?php

class Remote
{
    public const KEY_SECKILL_PRODUCT = 'seckill_product:';

    public const KEY_SECKILL_STOCK = 'seckill_total_nums';

    public const KEY_SECKILL_SALES = 'seckill_sold_nums';

    public const LUA_SECKILL = <<<'script'
        local seckill_key = KEYS[1]
        local seckill_total_key = ARGV[1]
        local seckill_sold_key = ARGV[2]
        local seckill_total_nums = tonumber(redis.call('HGET', seckill_key, seckill_total_key))
        local seckill_sold_nums = tonumber(redis.call('HGET', seckill_key, seckill_sold_key))
        -- 判断库存,增加订单数量,返回结果值
        if (seckill_total_nums > seckill_sold_nums) then
            return redis.call('HINCRBY', seckill_key, seckill_sold_key, 1)
        end
        return 0
script;

    /**
     * 远程扣减库存.
     *
     * @param \Redis $redis
     * @param int $product_id 产品ID
     * @return bool
     */
    public function deductStock(\Redis $redis, int $product_id = 1): bool
    {
        $result = $redis->eval(
            self::LUA_SECKILL,
            [
                self::KEY_SECKILL_PRODUCT.$product_id,
                self::KEY_SECKILL_STOCK,
                self::KEY_SECKILL_SALES,
            ],
            1
        );
        return $result > 0 ? true : false;
    }
}
