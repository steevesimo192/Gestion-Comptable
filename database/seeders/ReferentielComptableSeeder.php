<?php

namespace Database\Seeders;

use App\Models\ReferentielComptable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferentielComptableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        ReferentielComptable::updateOrCreate(
            ['code'=>'SYSCOHADA'],
            [
                'nom' => 'Système comptable OHADA',
                'pays_code' => null,
                'description' => 'Référentiel comptable applicable dans l’espace OHADA.',
                'version' => 'SYSCOHADA révisé',
                'date_mise_en_vigueur' => '2018-01-01',
                'date_fin_validite' => null,
                'actif' => true,
            ],
        );
    }
}
