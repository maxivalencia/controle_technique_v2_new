<?php

namespace App\Controller;

use App\Entity\CtHistorique;
use App\Entity\CtHistoriqueType;
//use App\Form\CtHistoriqueType;
use App\Repository\CtVisiteRepository;
use App\Repository\CtReceptionRepository;
use App\Repository\CtConstAvDedRepository;
use App\Form\CtHistoriqueTypeType;
use App\Repository\CtHistoriqueRepository;
use App\Repository\CtHistoriqueTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/ct_app_tableau_de_bord")
 */
class CtAppTableauDeBordController extends AbstractController
{
    /**
     * @Route("/", name="app_ct_app_tableau_de_bord")
     */
    public function index(CtVisiteRepository $ctVisiteRepository, CtReceptionRepository $ctReceptionRepository, CtConstAvDedRepository $ctConstAvDedRepository): Response
    {
        $jours = new ArrayCollection();
        $nombre_visites = new ArrayCollection();
        $nombre_receptions = new ArrayCollection();
        $nombre_constatations = new ArrayCollection();
        // calcul des 30 jours
        $nombre_de_jours = 30;
        $date = new \DateTime("2023-12-31");
        $i = 0;
        $date->modify('-'.$nombre_de_jours.' day');
        while($i < $nombre_de_jours){
            //$date->modify('-'.$i.' month');
            $date->modify('+1 day');
            $jours->add($date->format("d-m-Y"));
            $nombre_visites->add($ctVisiteRepository->findNombreVisite($date));
            $nombre_receptions->add($ctReceptionRepository->findNombreReception($date));
            $nombre_constatations->add($ctConstAvDedRepository->findNombreConstatation($date));
            $i++;
        }
        return $this->render('ct_app_tableau_de_bord/index.html.twig', [
            'jours' => $jours,
            'nombre_visites' => $nombre_visites,
            'nombre_receptions' => $nombre_receptions,
            'nombre_constatations' => $nombre_constatations,
        ]);
    }
}
