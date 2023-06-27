<?php

namespace App\Service;


use App\Entity\Appointment;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarService
{
    private UrlGeneratorInterface $router;
    private EntityManagerInterface $entityManager;
    private int $numberOfVisibleMonths;
    private DateTime $now;
    private DateTime $currentDate;
    private string $format = 'Y-m';

    public function __construct(UrlGeneratorInterface $router, EntityManagerInterface $entityManager, int $numberOfVisibleMonths = 2)
    {
        $this->router = $router;
        $this->numberOfVisibleMonths = $numberOfVisibleMonths;
        $this->now = new DateTime();
        $this->currentDate = $this->now;
        $this->entityManager = $entityManager;
    }

    public function getWorkHours(DateTime $date): array
    {
        return match ($date->format('D')) {
            'Sun' => [],
            'Sat' => ['09:00', '10:00', '11:00'],
            default => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00' , '17:00']
        };
    }

    public function tryChangingDate(string $date): self
    {
        $dateTime = DateTime::createFromFormat($this->format, $date);

        if ($dateTime !== false && $this->validateDate($dateTime)) {
            $this->currentDate = $dateTime;
        }

        return $this;
    }

    public function getCurrentDate(): DateTime
    {
        return $this->currentDate;
    }

    public function getPreviousDate(): null|DateTime
    {
        $previousDate = DateTime::createFromInterface($this->currentDate)->modify('-1 month');

        return $this->validateDate($previousDate) ? $previousDate : null;
    }

    public function generatePreviousMonthLink(): null|string
    {
        $pPreviousMonthLink = null;

        if ($this->getPreviousDate() instanceof DateTime){
            $pPreviousMonthLink = $this->router->generate('calendar_index', [
                'date' => $this->getPreviousDate()->format($this->format),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $pPreviousMonthLink;
    }


    public function getNextDate(): null|DateTime
    {
        $nextDate = DateTime::createFromInterface($this->currentDate)->modify('+1 month');

        return $this->validateDate($nextDate) ? $nextDate : null;
    }

    public function generateNextMonthLink(): null|string
    {
        $nextMonthLink = null;

        if ($this->getNextDate() instanceof DateTime){
            $nextMonthLink = $this->router->generate('calendar_index', [
                'date' => $this->getNextDate()->format($this->format),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $nextMonthLink;
    }

    private function validateDate(DateTime $date): bool
    {
        $before = (new DateTime($this->now->format('Y-m')))->modify('-' . $this->numberOfVisibleMonths . ' month');
        $after = (new DateTime($this->now->format('Y-m-t')))->modify('+' . $this->numberOfVisibleMonths . ' month');

        return ($before < $date && $date < $after);
    }

    public function buildCalendar(): array
    {
        $calendar = [
            0 => ['Mon' => [], 'Tue' => [], 'Wed' => [], 'Thu' => [], 'Fri' => [], 'Sat' => [], 'Sun' => []],
            1 => ['Mon' => [], 'Tue' => [], 'Wed' => [], 'Thu' => [], 'Fri' => [], 'Sat' => [], 'Sun' => []],
            2 => ['Mon' => [], 'Tue' => [], 'Wed' => [], 'Thu' => [], 'Fri' => [], 'Sat' => [], 'Sun' => []],
            3 => ['Mon' => [], 'Tue' => [], 'Wed' => [], 'Thu' => [], 'Fri' => [], 'Sat' => [], 'Sun' => []],
            4 => ['Mon' => [], 'Tue' => [], 'Wed' => [], 'Thu' => [], 'Fri' => [], 'Sat' => [], 'Sun' => []],
        ];

        $month = (new DateTime($this->getCurrentDate()->format('Y-m')));
        $row = 0;
        $numberOfDays = (int)$month->format('t');
        $monthNumber = $month->format('n');
        $appointments = $this->entityManager->getRepository(Appointment::class)->findByMonth($this->getCurrentDate());

        while ($row < 5) {
            for ($day = 1; $day <= $numberOfDays; $day++) {
                $dayOfWeek = $month->format('D');

                $date = new DateTime($month->format('d-m-Y'));
                $calendar[$row][$month->format('D')] = [
                    'date' => $date,
                    'dateNumber' => $month->format('d'),
                    'workHours' => $this->mapAppointmentsToWorkHours($date, $appointments)
                ];
                $month->modify('+1 day');

                if ($dayOfWeek === 'Sun' || $monthNumber !== $month->format('n')) {
                    $row++;
                    break;
                }
            }
        }

        return $calendar;
    }

    private function mapAppointmentsToWorkHours(DateTime $date, array &$appointments): array
    {
        $workHours = [];
        $dateFormat = $date->format('Y-m-d ');
        foreach ($this->getWorkHours($date) as $hour)
        {
            $dateTimeFormat = $dateFormat . $hour;

            $workHours[$dateTimeFormat] = ['hour' => $hour, 'taken' => false];

            foreach ($appointments as $key => $appointmentDate)
            {
                if($appointmentDate['date']->format('Y-m-d H:i') === $dateTimeFormat)
                {
                    $workHours[$dateTimeFormat]['taken'] = true;
                    unset($appointments[$key]);
                }
            }
        }

        return $workHours;
    }
}
