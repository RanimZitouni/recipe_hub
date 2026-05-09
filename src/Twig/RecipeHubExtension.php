<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class RecipeHubExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', [$this, 'timeAgo']),
            new TwigFilter('cooking_time_format', [$this, 'cookingTimeFormat']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('difficulty_stars', [$this, 'difficultyStars']),
        ];
    }

    public function timeAgo(?\DateTimeInterface $date): string
    {
        if (!$date) {
            return '';
        }

        $now = new \DateTimeImmutable('now', $date->getTimezone());
        $seconds = $now->getTimestamp() - $date->getTimestamp();
        $isFuture = $seconds < 0;
        $seconds = abs($seconds);

        if ($seconds < 60) {
            return $isFuture ? 'dans quelques secondes' : 'à l\'instant';
        }

        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) {
            $value = $minutes;
            $unit = $value > 1 ? 'minutes' : 'minute';
        } else {
            $hours = intdiv($minutes, 60);
            if ($hours < 24) {
                $value = $hours;
                $unit = $value > 1 ? 'heures' : 'heure';
            } else {
                $days = intdiv($hours, 24);
                if ($days < 30) {
                    $value = $days;
                    $unit = $value > 1 ? 'jours' : 'jour';
                } else {
                    $months = intdiv($days, 30);
                    if ($months < 12) {
                        $value = $months;
                        $unit = $value > 1 ? 'mois' : 'mois';
                    } else {
                        $years = intdiv($months, 12);
                        $value = $years;
                        $unit = $value > 1 ? 'ans' : 'an';
                    }
                }
            }
        }

        return $isFuture
            ? sprintf('dans %d %s', $value, $unit)
            : sprintf('il y a %d %s', $value, $unit);
    }

    public function cookingTimeFormat(?int $minutes): string
    {
        if ($minutes === null || $minutes <= 0) {
            return '';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return sprintf('%dmin', $mins);
        }

        if ($mins === 0) {
            return sprintf('%dh', $hours);
        }

        return sprintf('%dh%02d', $hours, $mins);
    }

    public function difficultyStars(?string $difficulty): string
    {
        $map = [
            'facile' => 1,
            'moyen' => 2,
            'difficile' => 3,
        ];

        $count = $map[$difficulty] ?? 0;

        return str_repeat('⭐', $count);
    }
}
