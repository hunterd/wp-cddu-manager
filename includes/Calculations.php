<?php
namespace CDDU_Manager;

class Calculations {
    /**
     * Calculate number of weeks between two dates.
     * Rounding: 'ceil' (default) counts any started week, 'floor' or 'round' also supported.
     */
    public static function nb_weeks(\DateTimeInterface $start, \DateTimeInterface $end, string $rounding = 'ceil'): int {
        $days = (int) ceil(($end->getTimestamp() - $start->getTimestamp()) / 86400) + 1;
        $weeks = $days / 7;
        return $rounding === 'floor' ? (int) floor($weeks) : ($rounding === 'round' ? (int) round($weeks) : (int) ceil($weeks));
    }

    public static function weekly_intensity(float $annual_hours, int $nb_weeks): float {
        if ($nb_weeks <= 0) { return 0.0; }
        return $annual_hours / $nb_weeks;
    }

    // Helpers for payroll calculations
    public static function Hp(float $annual_hours): float { return $annual_hours * (28/72); }
    public static function Ht(float $annual_hours): float { return $annual_hours + self::Hp($annual_hours); }
    public static function gross_amount(float $annual_hours, float $hourly_rate): float { return self::Ht($annual_hours) * $hourly_rate; }
    public static function usage_bonus(float $gross): float { return $gross * 0.06; }
    public static function paid_leave(float $gross): float { return $gross * 0.12; }
    public static function total(float $gross): float { return $gross + self::usage_bonus($gross) + self::paid_leave($gross); }

    /** Return a full array of calculations.
     * Accepts English keys: annual_hours, hourly_rate, start_date, end_date, rounding
     * Backward-compatible French aliases are accepted: H_a, taux_horaire, date_debut, date_fin, arrondi
     */
    public static function calculate(array $data): array {
        // Accept both English and French keys for backward compatibility
        $annual_hours = (float) ($data['annual_hours'] ?? $data['H_a'] ?? 0);
        $hourly_rate  = (float) ($data['hourly_rate'] ?? $data['taux_horaire'] ?? 0);
        $start_date   = $data['start_date'] ?? $data['date_debut'] ?? null;
        $end_date     = $data['end_date'] ?? $data['date_fin'] ?? null;
        $rounding     = $data['rounding'] ?? $data['arrondi'] ?? 'ceil';

        $start = new \DateTimeImmutable($start_date);
        $end   = new \DateTimeImmutable($end_date);

        $nb_weeks = self::nb_weeks($start, $end, $rounding);
        $intensity = self::weekly_intensity($annual_hours, $nb_weeks);
        $hp = self::Hp($annual_hours);
        $ht = self::Ht($annual_hours);
        $gross = self::gross_amount($annual_hours, $hourly_rate);
        $bonus = self::usage_bonus($gross);
        $paid_leave = self::paid_leave($gross);
        $total = $gross + $bonus + $paid_leave;

        // Return keys using English names but keep older keys mapping not included here to avoid duplication.
        return compact('annual_hours', 'hourly_rate', 'start_date', 'end_date', 'nb_weeks','intensity','hp','ht','gross','bonus','paid_leave','total');
    }

    /**
     * Calculate all CDDU contract values from mission data
     * Returns array with all calculated values for contract generation
     */
    public static function calculate_contract_values(array $mission_data): array {
        $calculations = self::calculate($mission_data);
        
        // Add additional fields needed for contract
        $calculations['start_date_formatted'] = !empty($calculations['start_date']) 
            ? (new \DateTimeImmutable($calculations['start_date']))->format('d/m/Y') 
            : '';
        $calculations['end_date_formatted'] = !empty($calculations['end_date']) 
            ? (new \DateTimeImmutable($calculations['end_date']))->format('d/m/Y') 
            : '';
        
        // Format monetary values
        $calculations['gross_formatted'] = number_format($calculations['gross'], 2, ',', ' ') . ' €';
        $calculations['bonus_formatted'] = number_format($calculations['bonus'], 2, ',', ' ') . ' €';
        $calculations['paid_leave_formatted'] = number_format($calculations['paid_leave'], 2, ',', ' ') . ' €';
        $calculations['total_formatted'] = number_format($calculations['total'], 2, ',', ' ') . ' €';
        
        // Format hours
        $calculations['hp_formatted'] = number_format($calculations['hp'], 2, ',', ' ') . 'h';
        $calculations['ht_formatted'] = number_format($calculations['ht'], 2, ',', ' ') . 'h';
        $calculations['intensity_formatted'] = number_format($calculations['intensity'], 2, ',', ' ') . 'h/week';
        
        return $calculations;
    }
}
