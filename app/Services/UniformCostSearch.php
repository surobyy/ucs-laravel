<?php

namespace App\Services;

/**
 * Uniform Cost Search (UCS)
 * Menjelajah node dengan total biaya jalur g(n) terkecil lebih dulu.
 * Optimal dan complete selama biaya tiap sisi > 0.
 */
class UniformCostSearch
{
    /** @var array<string, array<string, float>> */
    private array $graph = [];

    /**
     * @param array<int, array{0:string,1:string,2:float|int}> $edges  [dari, ke, biaya]
     */
    public function __construct(array $edges, bool $directed = false)
    {
        foreach ($edges as [$from, $to, $cost]) {
            if ($cost <= 0) {
                throw new \InvalidArgumentException("Biaya sisi $from-$to harus lebih dari 0.");
            }
            $this->graph[$from][$to] = (float) $cost;
            $this->graph[$to] ??= [];
            if (! $directed) {
                $this->graph[$to][$from] = (float) $cost;
            }
        }
    }

    /**
     * @return array{found:bool, path:string[], cost:float|null, steps:array}
     */
    public function search(string $start, string $goal): array
    {
        foreach ([$start, $goal] as $node) {
            if (! isset($this->graph[$node])) {
                throw new \InvalidArgumentException("Node '$node' tidak ada di graf.");
            }
        }

        // Frontier: [node, biaya total, jalur, nomor urut (tie-break FIFO)]
        $seq = 0;
        $frontier = [[$start, 0.0, [$start], $seq++]];
        $explored = [];
        $steps = [];

        while ($frontier) {
            usort($frontier, fn ($a, $b) => [$a[1], $a[3]] <=> [$b[1], $b[3]]);
            [$node, $cost, $path] = array_shift($frontier);

            if (isset($explored[$node])) {
                continue; // sudah dieksplorasi lewat jalur yang lebih murah
            }

            $steps[] = [
                'expand'   => $node,
                'cost'     => $cost,
                'frontier' => array_map(fn ($f) => "{$f[0]} ({$f[1]})", $frontier),
            ];

            // Goal test saat node DIEKSPANSI (bukan saat dimasukkan) agar hasil optimal
            if ($node === $goal) {
                return ['found' => true, 'path' => $path, 'cost' => $cost, 'steps' => $steps];
            }

            $explored[$node] = true;

            foreach ($this->graph[$node] as $next => $stepCost) {
                if (! isset($explored[$next])) {
                    $frontier[] = [$next, $cost + $stepCost, [...$path, $next], $seq++];
                }
            }
        }

        return ['found' => false, 'path' => [], 'cost' => null, 'steps' => $steps];
    }
}
