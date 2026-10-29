<?php

namespace App\Http\Controllers;

use App\Services\UniformCostSearch;
use Illuminate\Http\Request;

class UcsController extends Controller
{
    private const DEFAULT_EDGES = <<<TXT
Jember,Lumajang,50
Jember,Bondowoso,35
Jember,Banyuwangi,110
Lumajang,Probolinggo,75
Lumajang,Malang,100
Bondowoso,Situbondo,45
Situbondo,Probolinggo,100
Situbondo,Banyuwangi,120
Probolinggo,Pasuruan,40
Pasuruan,Surabaya,60
Pasuruan,Malang,60
Malang,Surabaya,90
TXT;

    public function index(Request $request)
    {
        $data = ['edges' => self::DEFAULT_EDGES, 'start' => 'Jember', 'goal' => 'Surabaya', 'result' => null, 'error' => null];

        if ($request->isMethod('post')) {
            $input = $request->validate([
                'edges' => 'required|string',
                'start' => 'required|string|max:50',
                'goal'  => 'required|string|max:50',
            ]);
            $data = array_merge($data, $input);

            try {
                $data['result'] = (new UniformCostSearch($this->parseEdges($input['edges'])))
                    ->search(trim($input['start']), trim($input['goal']));
            } catch (\InvalidArgumentException $e) {
                $data['error'] = $e->getMessage();
            }
        }

        return view('ucs.index', $data);
    }

    /** Format tiap baris: NodeA,NodeB,biaya */
    private function parseEdges(string $text): array
    {
        $edges = [];
        foreach (preg_split('/\R/', trim($text)) as $i => $line) {
            if (trim($line) === '') continue;
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) !== 3 || ! is_numeric($parts[2])) {
                throw new \InvalidArgumentException('Baris ' . ($i + 1) . ' tidak valid. Gunakan format: NodeA,NodeB,biaya');
            }
            $edges[] = [$parts[0], $parts[1], (float) $parts[2]];
        }
        return $edges;
    }
}
