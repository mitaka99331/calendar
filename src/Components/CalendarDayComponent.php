<?php


namespace App\Components;

use App\Service\CalendarService;
use DateTime;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class CalendarDayComponent
{
    public bool $renderDay = false;
    public array $day = [];
    public array $workHors = [];
    public bool $disableAppointments = false;
    public bool $currentDate= false;
    public string $appointmentDate = '';

    public function mount(array $day)
    {
        if(!empty($day))
        {
            $this->renderDay =true;
            $this->day = $day;

            $now = new DateTime(date('Y-m-d'));
            $this->appointmentDate = $day['date']->format('Y-m-d');

            if($this->appointmentDate === $now->format('Y-m-d')){
                $this->currentDate = true;
            }

            if($day['date'] < $now)
            {
                $this->disableAppointments = true;
            }

            $this->workHors = $day['workHours'];
        }
    }

}

