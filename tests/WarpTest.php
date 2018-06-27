<?php

namespace Tests;

use Warp\Warp;
use PHPUnit\Framework\TestCase;
use Warp\WarpInvalidDataException;

class WarpTest extends TestCase
{
    /** @test */
    function it_can_create_a_warp_instance()
    {
        $this->assertInstanceOf(Warp::class, new Warp());
    }

    /** @test */
    function a_array_of_data_can_be_set_to_prepare_the_drive()
    {
        $response = Warp::data([
            1,
            2,
            3,
        ])->get();

        $this->assertArraySubset([1,2,3], $response);
    }

    /** @test */
    function it_can_recive_many_arguments_an_return_as_array()
    {
        $response = Warp::data(1, 2, 3)->get();

        $this->assertArraySubset([1,2,3], $response);
    }

    /** @test */
    function it_can_count_an_array_of_data()
    {
        $count = Warp::data([1, 2, 3])->count();

        $this->assertEquals(3, $count);
    }

    /** @test */
    function it_can_count_many_arguments()
    {
        $count = Warp::data(1, 2, 3)->count();

        $this->assertEquals(3, $count);
    }

    /** @test */
    function it_can_sum_an_array_of_data()
    {
        $sum = Warp::data([1, 2, 3])->sum();

        $this->assertEquals(6, $sum);
    }

    /** @test */
    function it_can_sum_many_arguments()
    {
        $sum = Warp::data(1, 2, 3)->sum();

        $this->assertEquals(6, $sum);
    }

    /** @test */
    function it_can_sum_a_key_from_array_of_data()
    {
        $item = Warp::data([
            ['item' => 1, 'amount' => 100],
            ['item' => 3, 'amount' => 588],
        ])->sum('item');

        $amount = Warp::data([
            ['item' => 1, 'amount' => 100],
            ['item' => 3, 'amount' => 588],
        ])->sum('amount');


        $this->assertEquals(4, $item);
        $this->assertEquals(688, $amount);
    }

    /** @test */
    function it_can_map_an_array_of_data()
    {
        $items = Warp::data([
            ['item' => 1, 'amount' => 100],
            ['item' => 3, 'amount' => 588],
        ])->map(function ($items) {
            return $items['item'];
        });

        $amounts = Warp::data([
            ['item' => 1, 'amount' => 100],
            ['item' => 3, 'amount' => 588],
        ])->map(function ($items) {
            return $items['amount'];
        });

        $this->assertArraySubset([1, 3], $items);
        $this->assertArraySubset([100, 588], $amounts);
    }

    /** @test */
    function it_can_filter_an_array_of_data()
    {
        $items = Warp::data([
            ['item' => 1, 'amount' => 100],
            ['item' => 3, 'amount' => 588],
        ])->filter(function ($items) {
            return $items['item'] > 1;
        });

        $this->assertArraySubset([
            1 => ['item' => 3, 'amount' => 588]
        ], $items);
    }

    /** @test */
    function it_can_filter_false_null_empty_string_from_array_of_data()
    {
        $filter = Warp::data(['foo', false, -1, null, ''])->filter();

        $this->assertArraySubset([0 => 'foo', 2 => -1], $filter);
    }

    /** @test */
    function it_can_get_an_element_form_data()
    {
        $data = [
            'user' => 'Wilman',
            'item' => [
                'name' => 'Ipod',
                'amount' => 250
            ]
        ];

        $user = Warp::data($data)->get('user');
        $name = Warp::data($data)->get('item.name');
        $amount = Warp::data($data)->get('item.amount');

        $this->assertEquals($user, 'Wilman');
        $this->assertEquals($name, 'Ipod');
        $this->assertEquals($amount, 250);
    }

    /**
     * @test
     * @expectedException \Warp\WarpInvalidDataException
     */
    function it_can_thrown_an_exception_if_data_is_not_pass()
    {
        Warp::data();
    }

    /** @test */
    function it_can_flatten_an_array_of_elements()
    {
        $flatten = Warp::data([
            'user' => 'Wilman',
            'item' => [
                'name' => 'Ipod',
                'amount' => 250,
            ]
        ])->flatten();

        $this->assertEquals([
            'user' => 'Wilman',
            'name' => 'Ipod',
            'amount' => 250
        ], $flatten);
    }

    /** @test */
    function it_can_pluck_keys_from_an_array()
    {
        $pluck = Warp::data([
            [
                'name' => 'Ipod G1',
                'amount' => 160,
            ],
            [
                'name' => 'Ipod G2',
                'amount' => 240,
            ],
            [
                'name' => 'Ipod G3',
                'amount' => 343,
            ],
        ])->pluck('name');

        $this->assertEquals([
            'Ipod G1',
            'Ipod G2',
            'Ipod G3',
        ], $pluck);
    }

    /** @test */
    function it_can_flatMap_an_array_of_elements()
    {
        $flatMap = Warp::data([
            [
                'id' => 1,
                'name' => 'Ipod G1',
            ],
            [
                'id' => 2,
                'name' => 'Ipod G2',
            ],
            [
                'id' => 3,
                'name' => 'Ipod G3',
            ],
        ])->flatMap(function ($item) {
            return [$item['name'] => $item['id']];
        });

        $this->assertEquals([
            'Ipod G1' => 1,
            'Ipod G2' => 2,
            'Ipod G3' => 3,
        ], $flatMap);
    }
}
