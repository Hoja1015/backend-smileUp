<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Badge;
use App\Entity\ResidEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ── Activités ────────────────────────────────────────────
        $activities = [
            ['Ménage commun',       '🧹', 'hebdo',     30, 'Nettoyer ensemble les espaces communs'],
            ['Défi éco',            '🌱', 'hebdo',     50, 'Réduire l\'empreinte carbone de la résidence'],
            ['Organiser un dîner',  '🍽', 'special',   40, 'Préparer un repas collectif'],
            ['Tournoi FIFA',        '🎮', 'special',   50, 'Tournoi de football virtuel'],
            ['Check-in quotidien',  '☀️', 'quotidien', 10, 'Dire bonjour à la résidence chaque matin'],
            ['Humeur du jour',      '😊', 'quotidien',  5, 'Partager ton humeur du jour'],
        ];

        foreach ($activities as [$name, $emoji, $cat, $xp, $desc]) {
            $a = new Activity();
            $a->setName($name)
              ->setEmoji($emoji)
              ->setCategory($cat)
              ->setXpReward($xp)
              ->setDescription($desc);
            $manager->persist($a);
        }

        // ── Badges ───────────────────────────────────────────────
        // [slug, emoji, name, hint, xpRequired, activitiesRequired]
        $badges = [
            ['first-activity',  '🎯', 'Première activité',   'Lance ta première activité',        0,    1],
            ['five-activities', '🔥', 'Accro des activités', 'Complète 5 activités',              0,    5],
            ['ten-activities',  '⚡', 'Machine à XP',        'Complète 10 activités',             0,   10],
            ['bronze-master',   '🥉', 'Maître Bronze',       'Atteins 500 XP',                  500,   0],
            ['silver-master',   '🥈', 'Maître Silver',       'Atteins 2000 XP',                2000,   0],
            ['gold-master',     '🥇', 'Maître Gold',         'Atteins 5000 XP',                5000,   0],
            ['eco-hero',        '🌱', 'Éco Héros',           'Complète le défi éco',              50,   0],
        ];

        foreach ($badges as [$slug, $emoji, $name, $hint, $xpReq, $actReq]) {
            $b = new Badge();
            $b->setSlug($slug)
              ->setEmoji($emoji)
              ->setName($name)
              ->setHint($hint)
              ->setXpRequired($xpReq)
              ->setActivitiesRequired($actReq);
            $manager->persist($b);
        }

        // ── Événements ───────────────────────────────────────────
        // $now = new \DateTimeImmutable();
        $now = new \DateTime();
        $events = [
            [clone $now->modify('+5 days'), 'Soirée film',     '🎬', '20h30', 'Salle commune',  50],
            [clone $now->modify('+9 days'), 'Tournoi FIFA',    '🎮', '21h00', 'Salle commune',  50],
            [clone $now->modify('+12 days'),'Troc de plantes', '🌿', '16h00', 'Couloir B',      30],
            [clone $now->modify('+14 days'),'Pizza party',     '🍕', '19h30', 'Cuisine',        40],
        ];

        foreach ($events as [$date, $name, $emoji, $time, $place, $xp]) {
            $e = new ResidEvent();
            $e->setName($name)
              ->setEmoji($emoji)
              ->setDate($date)
              ->setTime($time)
              ->setPlace($place)
              ->setXpReward($xp);
            $manager->persist($e);
        }

        $manager->flush();
    }
}
