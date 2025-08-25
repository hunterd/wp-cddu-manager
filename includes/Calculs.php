<?php
namespace CDDU_Manager;

class Calculs {
    /**
     * Calcule les semaines (arrondi configurable: ceil par défaut = toute semaine entamée compte).
     */
    public static function nb_semaines(\DateTimeInterface $debut, \DateTimeInterface $fin, string $arrondi='ceil'): int {
        $days = (int) ceil(($fin->getTimestamp() - $debut->getTimestamp()) / 86400) + 1;
        $weeks = $days / 7;
        return $arrondi === 'floor' ? (int) floor($weeks) : ($arrondi === 'round' ? (int) round($weeks) : (int) ceil($weeks));
    }

    public static function intensite_hebdo(float $H_a, int $nb_semaines): float {
        if ($nb_semaines <= 0) { return 0.0; }
        return $H_a / $nb_semaines;
    }

    public static function Hp(float $H_a): float { return $H_a * (28/72); }
    public static function Ht(float $H_a): float { return $H_a + self::Hp($H_a); }
    public static function Mbrut(float $H_a, float $taux_horaire): float { return self::Ht($H_a) * $taux_horaire; }
    public static function prime_usage(float $M_brut): float { return $M_brut * 0.06; }
    public static function conges_payes(float $M_brut): float { return $M_brut * 0.12; }
    public static function total(float $M_brut): float { return $M_brut + self::prime_usage($M_brut) + self::conges_payes($M_brut); }

    /** Retourne un tableau complet des calculs */
    public static function calculer(array $data): array {
        $H_a = (float) ($data['H_a'] ?? 0);
        $taux = (float) ($data['taux_horaire'] ?? 0);
        $debut = new \DateTimeImmutable($data['date_debut']);
        $fin   = new \DateTimeImmutable($data['date_fin']);
        $arr   = $data['arrondi'] ?? 'ceil';

        $nb_semaines = self::nb_semaines($debut, $fin, $arr);
        $intensite   = self::intensite_hebdo($H_a, $nb_semaines);
        $H_p         = self::Hp($H_a);
        $H_t         = self::Ht($H_a);
        $M_brut      = self::Mbrut($H_a, $taux);
        $prime       = self::prime_usage($M_brut);
        $cp          = self::conges_payes($M_brut);
        $total       = $M_brut + $prime + $cp;

        return compact('nb_semaines','intensite','H_p','H_t','M_brut','prime','cp','total');
    }
}
