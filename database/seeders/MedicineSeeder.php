<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        if (Medicine::exists()) {
            return;
        }

        // Format: [name, type, generic_name, manufacturer]
        // Generic entries use the INN (International Nonproprietary Name).
        // Brand entries include the Philippine trade name and manufacturer.
        $medicines = [
            // ── ANALGESICS / ANTIPYRETICS ─────────────────────────────────────
            ['Paracetamol',            'generic', 'Paracetamol',              null],
            ['Biogesic',               'brand',   'Paracetamol',              'Unilab'],
            ['Tempra',                 'brand',   'Paracetamol',              'Pfizer Consumer'],
            ['Calpol',                 'brand',   'Paracetamol',              'GSK Consumer'],
            ['Panadol',                'brand',   'Paracetamol',              'GSK Consumer'],
            ['Tylenol',                'brand',   'Paracetamol',              'Johnson & Johnson'],
            ['Acemol',                 'brand',   'Paracetamol',              'Pascual Laboratories'],

            // ── NSAIDs ────────────────────────────────────────────────────────
            ['Ibuprofen',              'generic', 'Ibuprofen',                null],
            ['Medicol',                'brand',   'Ibuprofen',                'Pfizer Consumer'],
            ['Advil',                  'brand',   'Ibuprofen',                'Pfizer Consumer'],
            ['Brufen',                 'brand',   'Ibuprofen',                'Abbott'],
            ['Nurofen',                'brand',   'Ibuprofen',                'Reckitt Benckiser'],

            ['Mefenamic Acid',         'generic', 'Mefenamic Acid',           null],
            ['Ponstan',                'brand',   'Mefenamic Acid',           'Pfizer'],
            ['Dolfenal',               'brand',   'Mefenamic Acid',           'Unilab'],
            ['Feminax',                'brand',   'Mefenamic Acid',           'Reckitt'],

            ['Naproxen',               'generic', 'Naproxen',                 null],
            ['Flanax',                 'brand',   'Naproxen',                 'Bayer'],
            ['Aleve',                  'brand',   'Naproxen',                 'Bayer'],

            ['Diclofenac',             'generic', 'Diclofenac',               null],
            ['Voltaren',               'brand',   'Diclofenac',               'Novartis'],
            ['Cataflam',               'brand',   'Diclofenac',               'Novartis'],

            ['Celecoxib',              'generic', 'Celecoxib',                null],
            ['Celebrex',               'brand',   'Celecoxib',                'Pfizer'],

            ['Ketorolac',              'generic', 'Ketorolac',                null],
            ['Toradol',                'brand',   'Ketorolac',                'Roche'],
            ['Ketolac',                'brand',   'Ketorolac',                'Unilab'],

            // ── COMBINATION ANALGESICS (PH-specific) ─────────────────────────
            ['Alaxan FR',              'brand',   'Ibuprofen + Paracetamol',  'Unilab'],
            ['Alaxan',                 'brand',   'Ibuprofen + Paracetamol',  'Unilab'],

            // ── ANTIBIOTICS ───────────────────────────────────────────────────
            ['Amoxicillin',            'generic', 'Amoxicillin',              null],
            ['Amoxil',                 'brand',   'Amoxicillin',              'GSK'],
            ['Trimox',                 'brand',   'Amoxicillin',              'Apothecon'],

            ['Amoxicillin-Clavulanate','generic', 'Amoxicillin-Clavulanate',  null],
            ['Augmentin',              'brand',   'Amoxicillin-Clavulanate',  'GSK'],
            ['Clavam',                 'brand',   'Amoxicillin-Clavulanate',  'Ranbaxy'],
            ['Amoclan',                'brand',   'Amoxicillin-Clavulanate',  'Generics Pharma'],

            ['Azithromycin',           'generic', 'Azithromycin',             null],
            ['Zithromax',              'brand',   'Azithromycin',             'Pfizer'],
            ['Azee',                   'brand',   'Azithromycin',             'Cipla'],

            ['Ciprofloxacin',          'generic', 'Ciprofloxacin',            null],
            ['Ciprobay',               'brand',   'Ciprofloxacin',            'Bayer'],
            ['Cipromed',               'brand',   'Ciprofloxacin',            'Medis'],

            ['Cephalexin',             'generic', 'Cephalexin',               null],
            ['Keflex',                 'brand',   'Cephalexin',               'Eli Lilly'],

            ['Cefuroxime',             'generic', 'Cefuroxime',               null],
            ['Zinnat',                 'brand',   'Cefuroxime',               'GSK'],

            ['Clarithromycin',         'generic', 'Clarithromycin',           null],
            ['Klacid',                 'brand',   'Clarithromycin',           'AbbVie'],

            ['Doxycycline',            'generic', 'Doxycycline',              null],
            ['Vibramycin',             'brand',   'Doxycycline',              'Pfizer'],

            ['Metronidazole',          'generic', 'Metronidazole',            null],
            ['Flagyl',                 'brand',   'Metronidazole',            'Sanofi'],
            ['Metrogyl',               'brand',   'Metronidazole',            'J.B. Chemicals'],

            ['Cotrimoxazole',          'generic', 'Cotrimoxazole',            null],
            ['Bactrim',                'brand',   'Cotrimoxazole',            'Roche'],
            ['Septrin',                'brand',   'Cotrimoxazole',            'GSK'],

            ['Levofloxacin',           'generic', 'Levofloxacin',             null],
            ['Cravit',                 'brand',   'Levofloxacin',             'Daiichi-Sankyo'],
            ['Levaquin',               'brand',   'Levofloxacin',             'Janssen'],

            ['Erythromycin',           'generic', 'Erythromycin',             null],
            ['Ilosone',                'brand',   'Erythromycin',             'Eli Lilly'],

            // ── ANTIHISTAMINES ────────────────────────────────────────────────
            ['Cetirizine',             'generic', 'Cetirizine',               null],
            ['Zyrtec',                 'brand',   'Cetirizine',               'UCB / GSK'],
            ['Allerkid',               'brand',   'Cetirizine',               'Unilab'],
            ['Rigix',                  'brand',   'Cetirizine',               'Unilab'],

            ['Loratadine',             'generic', 'Loratadine',               null],
            ['Claritin',               'brand',   'Loratadine',               'Bayer'],
            ['Clarityne',              'brand',   'Loratadine',               'MSD'],
            ['Histrin',                'brand',   'Loratadine',               'Pharmalab'],

            ['Fexofenadine',           'generic', 'Fexofenadine',             null],
            ['Allegra',                'brand',   'Fexofenadine',             'Sanofi'],

            ['Levocetirizine',         'generic', 'Levocetirizine',           null],
            ['Xyzal',                  'brand',   'Levocetirizine',           'UCB'],

            ['Diphenhydramine',        'generic', 'Diphenhydramine',          null],
            ['Benadryl',               'brand',   'Diphenhydramine',          'Johnson & Johnson'],

            // ── COUGH & COLD ──────────────────────────────────────────────────
            ['Carbocisteine',          'generic', 'Carbocisteine',            null],
            ['Solmux',                 'brand',   'Carbocisteine',            'Robins'],
            ['Mucosol',                'brand',   'Carbocisteine',            'Generics Pharma'],

            ['Ambroxol',               'generic', 'Ambroxol',                 null],
            ['Mucosolvan',             'brand',   'Ambroxol',                 'Boehringer Ingelheim'],
            ['Ambrolex',               'brand',   'Ambroxol',                 'General de Pharma'],

            ['Bromhexine',             'generic', 'Bromhexine',               null],
            ['Bisolvon',               'brand',   'Bromhexine',               'Boehringer Ingelheim'],

            ['Guaifenesin',            'generic', 'Guaifenesin',              null],
            ['Robitussin',             'brand',   'Guaifenesin',              'Pfizer Consumer'],

            ['Decolgen',               'brand',   'Paracetamol + Phenylpropanolamine + Chlorphenamine', 'Unilab'],
            ['Neozep',                 'brand',   'Paracetamol + Phenylpropanolamine + Chlorphenamine', 'Unilab'],
            ['Tuseran Forte',          'brand',   'Dextromethorphan + Phenylpropanolamine + Chlorphenamine', 'Pascual Laboratories'],

            // ── PHILIPPINE HERBAL MEDICINES (PITAHC-approved) ─────────────────
            ['Lagundi',                'generic', 'Vitex negundo',            null],
            ['Ascof Lagundi',          'brand',   'Vitex negundo',            'Pascual Laboratories'],
            ['Likha Lagundi',          'brand',   'Vitex negundo',            'Natrapharm'],
            ['Lagundi Tablet',         'brand',   'Vitex negundo',            'Various'],

            ['Sambong',                'generic', 'Blumea balsamifera',       null],
            ['Marlieve',               'brand',   'Blumea balsamifera',       'Pascual Laboratories'],
            ['Prostone',               'brand',   'Blumea balsamifera',       'Pascual Laboratories'],

            ['Tsaang Gubat',           'generic', 'Ehretia microphylla',      null],
            ['Yerba Buena',            'generic', 'Mentha cordifolia',        null],

            // ── GI / ANTACIDS ─────────────────────────────────────────────────
            ['Omeprazole',             'generic', 'Omeprazole',               null],
            ['Losec',                  'brand',   'Omeprazole',               'AstraZeneca'],
            ['Omepron',                'brand',   'Omeprazole',               'Unilab'],
            ['Mepral',                 'brand',   'Omeprazole',               'Pharex'],

            ['Esomeprazole',           'generic', 'Esomeprazole',             null],
            ['Nexium',                 'brand',   'Esomeprazole',             'AstraZeneca'],

            ['Pantoprazole',           'generic', 'Pantoprazole',             null],
            ['Protonix',               'brand',   'Pantoprazole',             'Pfizer'],

            ['Famotidine',             'generic', 'Famotidine',               null],
            ['Pepcid',                 'brand',   'Famotidine',               'Johnson & Johnson'],

            ['Kremil-S',               'brand',   'Aluminum Hydroxide + Magnesium Hydroxide + Simethicone', 'Unilab'],
            ['Maalox',                 'brand',   'Aluminum Hydroxide + Magnesium Hydroxide', 'Sanofi'],

            ['Domperidone',            'generic', 'Domperidone',              null],
            ['Motilium',               'brand',   'Domperidone',              'Johnson & Johnson'],
            ['Vomistop',               'brand',   'Domperidone',              'Cipla'],
            ['Nauzene',                'brand',   'Domperidone',              'Hanlim'],

            ['Metoclopramide',         'generic', 'Metoclopramide',           null],
            ['Plasil',                 'brand',   'Metoclopramide',           'Sanofi'],

            ['Loperamide',             'generic', 'Loperamide',               null],
            ['Diatabs',                'brand',   'Loperamide',               'Unilab'],
            ['Imodium',                'brand',   'Loperamide',               'Johnson & Johnson'],
            ['Entdiar',                'brand',   'Loperamide',               'Pharex'],

            ['Hyoscine Butylbromide',  'generic', 'Hyoscine Butylbromide',    null],
            ['Buscopan',               'brand',   'Hyoscine Butylbromide',    'Boehringer Ingelheim'],

            ['Simethicone',            'generic', 'Simethicone',              null],

            // ── CARDIOVASCULAR ────────────────────────────────────────────────
            ['Amlodipine',             'generic', 'Amlodipine',               null],
            ['Norvasc',                'brand',   'Amlodipine',               'Pfizer'],
            ['Amlodac',                'brand',   'Amlodipine',               'Zydus'],
            ['Tensio',                 'brand',   'Amlodipine',               'Unilab'],

            ['Nifedipine',             'generic', 'Nifedipine',               null],
            ['Adalat',                 'brand',   'Nifedipine',               'Bayer'],
            ['Calcibloc',              'brand',   'Nifedipine',               'Unilab'],

            ['Losartan',               'generic', 'Losartan',                 null],
            ['Cozaar',                 'brand',   'Losartan',                 'MSD'],

            ['Valsartan',              'generic', 'Valsartan',                null],
            ['Diovan',                 'brand',   'Valsartan',                'Novartis'],

            ['Lisinopril',             'generic', 'Lisinopril',               null],
            ['Zestril',                'brand',   'Lisinopril',               'AstraZeneca'],

            ['Enalapril',              'generic', 'Enalapril',                null],
            ['Vasotec',                'brand',   'Enalapril',                'MSD'],
            ['Enaril',                 'brand',   'Enalapril',                'Unilab'],

            ['Captopril',              'generic', 'Captopril',                null],
            ['Capoten',                'brand',   'Captopril',                'Par Pharmaceutical'],

            ['Metoprolol',             'generic', 'Metoprolol',               null],
            ['Betaloc',                'brand',   'Metoprolol',               'AstraZeneca'],

            ['Atenolol',               'generic', 'Atenolol',                 null],
            ['Tenormin',               'brand',   'Atenolol',                 'AstraZeneca'],

            ['Carvedilol',             'generic', 'Carvedilol',               null],
            ['Coreg',                  'brand',   'Carvedilol',               'GSK'],

            ['Furosemide',             'generic', 'Furosemide',               null],
            ['Lasix',                  'brand',   'Furosemide',               'Sanofi'],

            ['Spironolactone',         'generic', 'Spironolactone',           null],
            ['Aldactone',              'brand',   'Spironolactone',           'Pfizer'],

            ['Aspirin',                'generic', 'Aspirin',                  null],
            ['Ecosprin',               'brand',   'Aspirin',                  'USV'],
            ['Aspirin Protect',        'brand',   'Aspirin',                  'Bayer'],

            ['Clopidogrel',            'generic', 'Clopidogrel',              null],
            ['Plavix',                 'brand',   'Clopidogrel',              'Sanofi'],

            ['Atorvastatin',           'generic', 'Atorvastatin',             null],
            ['Lipitor',                'brand',   'Atorvastatin',             'Pfizer'],
            ['Torvastat',              'brand',   'Atorvastatin',             'Unilab'],

            ['Simvastatin',            'generic', 'Simvastatin',              null],
            ['Zocor',                  'brand',   'Simvastatin',              'MSD'],
            ['Simvacor',               'brand',   'Simvastatin',              'Unilab'],

            ['Rosuvastatin',           'generic', 'Rosuvastatin',             null],
            ['Crestor',                'brand',   'Rosuvastatin',             'AstraZeneca'],

            ['Digoxin',                'generic', 'Digoxin',                  null],
            ['Lanoxin',                'brand',   'Digoxin',                  'GSK'],

            // ── DIABETES ──────────────────────────────────────────────────────
            ['Metformin',              'generic', 'Metformin',                null],
            ['Glucophage',             'brand',   'Metformin',                'Merck'],

            ['Glibenclamide',          'generic', 'Glibenclamide',            null],
            ['Daonil',                 'brand',   'Glibenclamide',            'Sanofi'],
            ['Euglucon',               'brand',   'Glibenclamide',            'Boehringer Ingelheim'],

            ['Glimepiride',            'generic', 'Glimepiride',              null],
            ['Amaryl',                 'brand',   'Glimepiride',              'Sanofi'],

            ['Glipizide',              'generic', 'Glipizide',                null],
            ['Glucotrol',              'brand',   'Glipizide',                'Pfizer'],

            ['Sitagliptin',            'generic', 'Sitagliptin',              null],
            ['Januvia',                'brand',   'Sitagliptin',              'MSD'],

            ['Insulin',                'generic', 'Insulin',                  null],
            ['Humulin',                'brand',   'Insulin',                  'Eli Lilly'],
            ['Lantus',                 'brand',   'Insulin Glargine',         'Sanofi'],
            ['Novolin',                'brand',   'Insulin',                  'Novo Nordisk'],

            // ── RESPIRATORY / ASTHMA ──────────────────────────────────────────
            ['Salbutamol',             'generic', 'Salbutamol',               null],
            ['Ventolin',               'brand',   'Salbutamol',               'GSK'],
            ['Asthalin',               'brand',   'Salbutamol',               'Cipla'],
            ['Salbuair',               'brand',   'Salbutamol',               'Various'],

            ['Ipratropium Bromide',     'generic', 'Ipratropium Bromide',      null],
            ['Atrovent',               'brand',   'Ipratropium Bromide',      'Boehringer Ingelheim'],

            ['Tiotropium',             'generic', 'Tiotropium',               null],
            ['Spiriva',                'brand',   'Tiotropium',               'Boehringer Ingelheim'],

            ['Montelukast',            'generic', 'Montelukast',              null],
            ['Singulair',              'brand',   'Montelukast',              'MSD'],
            ['Montair',                'brand',   'Montelukast',              'Cipla'],

            ['Budesonide',             'generic', 'Budesonide',               null],
            ['Pulmicort',              'brand',   'Budesonide',               'AstraZeneca'],

            ['Fluticasone',            'generic', 'Fluticasone',              null],
            ['Flixotide',              'brand',   'Fluticasone',              'GSK'],
            ['Flovent',                'brand',   'Fluticasone',              'GSK'],

            ['Theophylline',           'generic', 'Theophylline',             null],
            ['Theo-24',                'brand',   'Theophylline',             'UCB'],

            // ── CORTICOSTEROIDS ───────────────────────────────────────────────
            ['Prednisolone',           'generic', 'Prednisolone',             null],
            ['Deltacortril',           'brand',   'Prednisolone',             'Pfizer'],

            ['Prednisone',             'generic', 'Prednisone',               null],

            ['Dexamethasone',          'generic', 'Dexamethasone',            null],
            ['Decadron',               'brand',   'Dexamethasone',            'Organon'],

            ['Methylprednisolone',     'generic', 'Methylprednisolone',       null],
            ['Medrol',                 'brand',   'Methylprednisolone',       'Pfizer'],
            ['Solu-Medrol',            'brand',   'Methylprednisolone',       'Pfizer'],

            ['Hydrocortisone',         'generic', 'Hydrocortisone',           null],

            // ── THYROID ───────────────────────────────────────────────────────
            ['Levothyroxine',          'generic', 'Levothyroxine',            null],
            ['Euthyrox',               'brand',   'Levothyroxine',            'Merck'],
            ['Synthroid',              'brand',   'Levothyroxine',            'AbbVie'],

            ['Propylthiouracil',       'generic', 'Propylthiouracil',         null],

            ['Carbimazole',            'generic', 'Carbimazole',              null],
            ['Neo-Mercazole',          'brand',   'Carbimazole',              'Roche'],

            // ── VITAMINS & SUPPLEMENTS (prominent in PH) ─────────────────────
            ['Vitamin C',              'generic', 'Ascorbic Acid',            null],
            ['Ascorbic Acid',          'generic', 'Ascorbic Acid',            null],
            ['Ceelin',                 'brand',   'Ascorbic Acid',            'Unilab'],
            ['Fern-C',                 'brand',   'Sodium Ascorbate',         'Pascual Laboratories'],
            ['Celin',                  'brand',   'Ascorbic Acid',            'GSK'],

            ['Vitamin B Complex',      'generic', 'Vitamin B Complex',        null],
            ['Neurobion',              'brand',   'Vitamin B Complex',        'Merck'],
            ['Becosules',              'brand',   'Vitamin B Complex',        'Pfizer'],
            ['Benutrex',               'brand',   'Vitamin B Complex',        'Pascual Laboratories'],

            ['Methylcobalamin',        'generic', 'Methylcobalamin',          null],
            ['Methycobal',             'brand',   'Methylcobalamin',          'Eisai'],
            ['Vitamin B12',            'generic', 'Cyanocobalamin',           null],
            ['Cobavit',                'brand',   'Cyanocobalamin',           'Pascual Laboratories'],

            ['Multivitamins',          'generic', 'Multivitamins',            null],
            ['Enervon',                'brand',   'Multivitamins',            'Wyeth / Pfizer Consumer'],
            ['Centrum',                'brand',   'Multivitamins',            'Pfizer Consumer'],
            ['Cherifer',               'brand',   'Multivitamins',            'Unilab'],
            ['Ceelin Plus',            'brand',   'Multivitamins + Ascorbic Acid', 'Unilab'],
            ['Stresstabs',             'brand',   'Multivitamins',            'Pfizer Consumer'],

            ['Calcium Carbonate',      'generic', 'Calcium Carbonate',        null],
            ['Caltrate',               'brand',   'Calcium Carbonate',        'Wyeth / Pfizer Consumer'],
            ['Calcimax',               'brand',   'Calcium Carbonate',        'Generics Pharma'],

            ['Ferrous Sulfate',        'generic', 'Ferrous Sulfate',          null],
            ['Sangobion',              'brand',   'Ferrous + Multivitamins',  'Merck'],
            ['Ferocon',                'brand',   'Ferrous Sulfate',          'Pascual Laboratories'],

            ['Folic Acid',             'generic', 'Folic Acid',               null],
            ['Folart',                 'brand',   'Folic Acid',               'Unilab'],

            ['Zinc Sulfate',           'generic', 'Zinc Sulfate',             null],
            ['Zincman',                'brand',   'Zinc Sulfate',             'Unilab'],

            ['Omega-3 Fatty Acids',    'generic', 'Omega-3 Fatty Acids',      null],
            ['Scott\'s Emulsion',      'brand',   'Cod Liver Oil',            'GlaxoSmithKline'],

            // ── ANTIFUNGALS ───────────────────────────────────────────────────
            ['Fluconazole',            'generic', 'Fluconazole',              null],
            ['Diflucan',               'brand',   'Fluconazole',              'Pfizer'],
            ['Flucozid',               'brand',   'Fluconazole',              'Generics Pharma'],

            ['Clotrimazole',           'generic', 'Clotrimazole',             null],
            ['Canesten',               'brand',   'Clotrimazole',             'Bayer'],

            ['Itraconazole',           'generic', 'Itraconazole',             null],
            ['Sporanox',               'brand',   'Itraconazole',             'Janssen'],

            ['Ketoconazole',           'generic', 'Ketoconazole',             null],
            ['Nizoral',                'brand',   'Ketoconazole',             'Janssen'],

            // ── ANTIVIRALS ────────────────────────────────────────────────────
            ['Acyclovir',              'generic', 'Acyclovir',                null],
            ['Zovirax',                'brand',   'Acyclovir',                'GSK'],
            ['Acyvir',                 'brand',   'Acyclovir',                'Cipla'],

            ['Oseltamivir',            'generic', 'Oseltamivir',              null],
            ['Tamiflu',                'brand',   'Oseltamivir',              'Roche'],

            // ── NEUROLOGY / PSYCHIATRY ────────────────────────────────────────
            ['Gabapentin',             'generic', 'Gabapentin',               null],
            ['Neurontin',              'brand',   'Gabapentin',               'Pfizer'],

            ['Pregabalin',             'generic', 'Pregabalin',               null],
            ['Lyrica',                 'brand',   'Pregabalin',               'Pfizer'],

            ['Carbamazepine',          'generic', 'Carbamazepine',            null],
            ['Tegretol',               'brand',   'Carbamazepine',            'Novartis'],

            ['Phenytoin',              'generic', 'Phenytoin',                null],
            ['Dilantin',               'brand',   'Phenytoin',                'Pfizer'],

            ['Valproic Acid',          'generic', 'Valproic Acid',            null],
            ['Depakote',               'brand',   'Valproic Acid',            'AbbVie'],

            ['Levetiracetam',          'generic', 'Levetiracetam',            null],
            ['Keppra',                 'brand',   'Levetiracetam',            'UCB'],

            ['Amitriptyline',          'generic', 'Amitriptyline',            null],
            ['Elavil',                 'brand',   'Amitriptyline',            'Abbott'],

            ['Fluoxetine',             'generic', 'Fluoxetine',               null],
            ['Prozac',                 'brand',   'Fluoxetine',               'Eli Lilly'],

            ['Sertraline',             'generic', 'Sertraline',               null],
            ['Zoloft',                 'brand',   'Sertraline',               'Pfizer'],

            ['Diazepam',               'generic', 'Diazepam',                 null],
            ['Valium',                 'brand',   'Diazepam',                 'Roche'],

            ['Clonazepam',             'generic', 'Clonazepam',               null],
            ['Rivotril',               'brand',   'Clonazepam',               'Roche'],

            ['Alprazolam',             'generic', 'Alprazolam',               null],
            ['Xanax',                  'brand',   'Alprazolam',               'Pfizer'],

            ['Risperidone',            'generic', 'Risperidone',              null],
            ['Risperdal',              'brand',   'Risperidone',              'Janssen'],

            ['Haloperidol',            'generic', 'Haloperidol',              null],
            ['Haldol',                 'brand',   'Haloperidol',              'Janssen'],

            ['Levodopa + Carbidopa',   'generic', 'Levodopa + Carbidopa',     null],
            ['Sinemet',                'brand',   'Levodopa + Carbidopa',     'MSD'],

            // ── OPHTHALMOLOGY ─────────────────────────────────────────────────
            ['Timolol',                'generic', 'Timolol',                  null],
            ['Timoptic',               'brand',   'Timolol',                  'MSD'],

            ['Latanoprost',            'generic', 'Latanoprost',              null],
            ['Xalatan',                'brand',   'Latanoprost',              'Pfizer'],

            // ── UROLOGY ───────────────────────────────────────────────────────
            ['Tamsulosin',             'generic', 'Tamsulosin',               null],
            ['Harnal',                 'brand',   'Tamsulosin',               'Astellas'],

            ['Finasteride',            'generic', 'Finasteride',              null],
            ['Proscar',                'brand',   'Finasteride',              'MSD'],

            // ── DERMATOLOGY ───────────────────────────────────────────────────
            ['Tretinoin',              'generic', 'Tretinoin',                null],
            ['Retin-A',                'brand',   'Tretinoin',                'Janssen'],

            ['Mupirocin',              'generic', 'Mupirocin',                null],
            ['Bactroban',              'brand',   'Mupirocin',                'GSK'],

            ['Betamethasone',          'generic', 'Betamethasone',            null],
            ['Betnovate',              'brand',   'Betamethasone',            'GSK'],

            // ── ANALGESICS (Opioid/Strong) ────────────────────────────────────
            ['Tramadol',               'generic', 'Tramadol',                 null],
            ['Tramal',                 'brand',   'Tramadol',                 'Grünenthal'],
            ['Ultram',                 'brand',   'Tramadol',                 'Janssen'],

            ['Morphine',               'generic', 'Morphine',                 null],
        ];

        $now   = now();
        $rows  = array_map(fn ($m) => [
            'name'         => $m[0],
            'type'         => $m[1],
            'generic_name' => $m[2],
            'manufacturer' => $m[3],
            'created_at'   => $now,
            'updated_at'   => $now,
        ], $medicines);

        // Insert in chunks to avoid hitting parameter limits
        foreach (array_chunk($rows, 100) as $chunk) {
            Medicine::insert($chunk);
        }
    }
}
