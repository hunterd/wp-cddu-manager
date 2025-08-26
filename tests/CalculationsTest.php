<?php

use CDDU_Manager\Calculations;
use PHPUnit\Framework\TestCase;

class CalculationsTest extends TestCase
{
    public function testBasicFormulas()
    {
        $data = [
            'annual_hours' => 72.0,
            'hourly_rate' => 20.0,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'rounding' => 'ceil'
        ];

        $result = Calculations::calculate($data);

        // H_p = 72 * 28/72 = 28
    $this->assertEquals(28.0, $result['hp'], '', 0.0001);
        // H_t = 72 + 28 = 100
    $this->assertEquals(100.0, $result['ht'], '', 0.0001);
        // M_brut = 100 * 20 = 2000
    $this->assertEquals(2000.0, $result['gross'], '', 0.0001);
        // prime = 2000 * 0.06 = 120
    $this->assertEquals(120.0, $result['bonus'], '', 0.0001);
        // cp = 2000 * 0.12 = 240
    $this->assertEquals(240.0, $result['paid_leave'], '', 0.0001);
        // total = 2360
    $this->assertEquals(2360.0, $result['total'], '', 0.0001);

        // nb_semaines between 2025-01-01 and 2025-01-31 = 31 days -> ceil(31/7)=5 -> intensite = 72/5 = 14.4
    $this->assertEquals(5, $result['nb_weeks']);
    $this->assertEquals(14.4, $result['intensity'], '', 0.0001);
    }
}
