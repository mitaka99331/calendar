<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentFormType;
use App\Repository\AppointmentRepository;
use App\Service\CalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class CalendarController extends AbstractController
{
    #[Route('/', name: 'calendar_index')]
    public function index(Request $request, CalendarService $calendarService, AppointmentRepository $repository): Response
    {
        if ($request->query->has('date')) {
            $calendarService->tryChangingDate($request->query->get('date'));
        }

        $form = $this->createForm(AppointmentFormType::class, new Appointment());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $date = $form->get('date')->getData();
            $workHours = $calendarService->getWorkHours($date);

            if(in_array($date->format('H:i'), $workHours))
            {
                $repository->save($form->getData(), true);

                return $this->redirectToRoute('calendar_index', ['date' => $request->query->get('date')]);
            }
        }

        return $this->render('calendar/index.html.twig', [
            'now' => new DateTime(),
            'currentDate' => $calendarService->getCurrentDate(),
            'previousDateLink' => $calendarService->generatePreviousMonthLink(),
            'nextDateLink' => $calendarService->generateNextMonthLink(),
            'calendar' => $calendarService->buildCalendar(),
            'form' => $form,
        ]);
    }
}
