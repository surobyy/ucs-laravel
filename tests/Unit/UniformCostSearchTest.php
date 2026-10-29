<?php

namespace Tests\Unit;

use App\Services\UniformCostSearch;
use PHPUnit\Framework\TestCase;

class UniformCostSearchTest extends TestCase
{
    public function test_memilih_jalur_termurah_bukan_hop_tersedikit(): void
    {
        $ucs = new UniformCostSearch([
            ['A', 'D', 10],
            ['A', 'B', 2], ['B', 'C', 2], ['C', 'D', 2],
        ]);
        $r = $ucs->search('A', 'D');

        $this->assertTrue($r['found']);
        $this->assertSame(['A', 'B', 'C', 'D'], $r['path']);
        $this->assertSame(6.0, $r['cost']);
    }

    public function test_tidak_ada_jalur(): void
    {
        $r = (new UniformCostSearch([['A', 'B', 1], ['C', 'D', 1]]))->search('A', 'D');
        $this->assertFalse($r['found']);
    }

    public function test_start_sama_dengan_goal(): void
    {
        $r = (new UniformCostSearch([['A', 'B', 1]]))->search('A', 'A');
        $this->assertSame(0.0, $r['cost']);
    }

    public function test_biaya_nol_ditolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new UniformCostSearch([['A', 'B', 0]]);
    }
}
