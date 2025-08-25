<?php

use CDDU_Manager\Calculs;
use PHPUnit\Framework\TestCase;

class CalculsTest extends TestCase
{
    public function testBasicFormulas()
    {
        $data = [
            'H_a' => 72.0,
            'taux_horaire' => 20.0,
            'date_debut' => '2025-01-01',
            'date_fin' => '2025-01-31',
            'arrondi' => 'ceil'
        ];

        $result = Calculs::calculer($data);

        // H_p = 72 * 28/72 = 28
        $this->assertEquals(28.0, $result['H_p'], '', 0.0001);
        // H_t = 72 + 28 = 100
        $this->assertEquals(100.0, $result['H_t'], '', 0.0001);
        // M_brut = 100 * 20 = 2000
        $this->assertEquals(2000.0, $result['M_brut'], '', 0.0001);
        // prime = 2000 * 0.06 = 120
        $this->assertEquals(120.0, $result['prime'], '', 0.0001);
        // cp = 2000 * 0.12 = 240
        $this->assertEquals(240.0, $result['cp'], '', 0.0001);
        // total = 2360
        $this->assertEquals(2360.0, $result['total'], '', 0.0001);

        // nb_semaines between 2025-01-01 and 2025-01-31 = 31 days -> ceil(31/7)=5 -> intensite = 72/5 = 14.4
        $this->assertEquals(5, $result['nb_semaines']);
        $this->assertEquals(14.4, $result['intensite'], '', 0.0001);
    }
}
