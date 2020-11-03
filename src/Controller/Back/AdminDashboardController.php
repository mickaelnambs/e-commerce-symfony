<?php

namespace App\Controller\Back;

use App\Service\StatsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class AdminDashboardController.
 * 
 * @IsGranted("ROLE_ADMIN")
 * 
 * @author Mickael Nambinintsoa <mickael.nambinintsoa07081999@gmail.com>
 */
class AdminDashboardController extends AbstractController
{
    /**
     * Dashboard.
     * 
     * @Route("/admin", name="admin_dashboard_index")
     *
     * @param StatsService $statsService
     * 
     * @return Response
     */
    public function index(StatsService $statsService): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $statsService->getStats()
        ]);
    }
}
