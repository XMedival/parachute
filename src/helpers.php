<?php

use Parachute\Base\App;
use Parachute\Support\Env;
use Parachute\Contracts\View\View;

if (!function_exists('app')) {
    function app($abstract = null)
    {
        $instance = App::getInstance();

        if ($abstract) {
            return $instance->make($abstract);
        }

        return $instance;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        $config = app('config');

        return $config->get($key, $default);
    }
}

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('join_paths')) {
    function join_paths($basePath, ...$paths): string
    {
        foreach ($paths as $index => $path) {
            if (empty($path) && $path !== '0') {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return $basePath . implode('', $paths);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = '')
    {
        $base_path = app('base_path');
        return join_paths($base_path, $path);
    }
}

if (!function_exists('view')) {
    function view(string $view = '', array $data = []): View
    {
        return app('view')->make($view, $data);
    }
}

if (!function_exists('pluralize')) {
    function pluralize(string $word): string
    {
        $irregurals = [
            'person' => 'people',
            'child' => 'children',
            'mouse' => 'mice',
            'goose' => 'geese',
            'ox' => 'oxen',
            'leaf' => 'leaves',
            'cactus' => 'cacti',
            'focus' => 'foci',
            'fungus' => 'fungi',
            'nucleus' => 'nuclei',
            'syllabus' => 'syllabi',
            'analysis' => 'analyses',
            'diagnosis' => 'diagnoses',
            'oasis' => 'oases',
            'thesis' => 'theses',
            'crisis' => 'crises',
            'phenomenon' => 'phenomena',
            'addendum' => 'addenda',
            'aircraft' => 'aircraft',
            'alumna' => 'alumnae',
            'alumnus' => 'alumni',
            'analysis' => 'analyses',
            'antenna' => 'antennae',
            'antithesis' => 'antitheses',
            'apex' => 'apices',
            'appendix' => 'appendices',
            'axis' => 'axes',
            'bacillus' => 'bacilli',
            'bacterium' => 'bacteria',
            'basis' => 'bases',
            'beau' => 'beaux',
            'bison' => 'bison',
            'bureau' => 'bureaux',
            'cactus' => 'cacti',
            'child' => 'children',
            'codex' => 'codices',
            'concerto' => 'concerti',
            'corpus' => 'corpora',
            'crisis' => 'crises',
            'criterion' => 'criteria',
            'curriculum' => 'curricula',
            'datum' => 'data',
            'deer' => 'deer',
            'diagnosis' => 'diagnoses',
            'die' => 'dice',
            'dwarf' => 'dwarves',
            'ellipsis' => 'ellipses',
            'erratum' => 'errata',
            'faux' => 'faux',
            'pas' => 'pas',
            'fez' => 'fezzes',
            'fish' => 'fish',
            'focus' => 'foci',
            'foot' => 'feet',
            'formula' => 'formulae',
            'fungus' => 'fungi',
            'genus' => 'genera',
            'goose' => 'geese',
            'graffito' => 'graffiti',
            'grouse' => 'grouse',
            'half' => 'halves',
            'hoof' => 'hooves',
            'hypothesis' => 'hypotheses',
            'index' => 'indices',
            'larva' => 'larvae',
            'libretto' => 'libretti',
            'loaf' => 'loaves',
            'locus' => 'loci',
            'louse' => 'lice',
            'man' => 'men',
            'matrix' => 'matrices',
            'medium' => 'media',
            'memorandum' => 'memoranda',
            'minutia' => 'minutiae',
            'moose' => 'moose',
            'mouse' => 'mice',
            'nebula' => 'nebulae',
            'nucleus' => 'nuclei',
            'oasis' => 'oases',
            'offspring' => 'offspring',
            'opus' => 'opera',
            'ovum' => 'ova',
            'ox' => 'oxen',
            'parenthesis' => 'parentheses',
            'phenomenon' => 'phenomena',
            'phylum' => 'phyla',
            'quiz' => 'quizzes',
            'radius' => 'radii',
            'referendum' => 'referenda',
            'salmon' => 'salmon',
            'scarf' => 'scarves',
            'self' => 'selves',
            'series' => 'series',
            'sheep' => 'sheep',
            'shrimp' => 'shrimp',
            'species' => 'species',
            'stimulus' => 'stimuli',
            'stratum' => 'strata',
            'swine' => 'swine',
            'syllabus' => 'syllabi',
            'symposium' => 'symposia',
            'synopsis' => 'synopses',
            'tableau' => 'tableaux',
            'thesis' => 'theses',
            'thief' => 'thieves',
            'tooth' => 'teeth',
            'trout' => 'trout',
            'tuna' => 'tuna',
            'vertebra' => 'vertebrae',
            'vertex' => 'vertices',
            'vita' => 'vitae',
            'vortex' => 'vortices',
            'wharf' => 'wharves',
            'wife' => 'wives',
            'wolf' => 'wolves',
            'woman' => 'women',
        ];

        if (isset($irregurals[$word])) {
            return $irregurals[$word];
        }

        if (preg_match('/[^aeiou]y$/', $word)) {
            return preg_replace('/y$/', 'ies', $word);
        }

        if (preg_match('/(s|sh|ch|x|z)$/', $word)) {
            return $word . 'es';
        }

        return $word . 's';
    }
}
