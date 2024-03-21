<?php

namespace App\Controller;

use App\Repository\CtMotifTarifRepository;
use App\Repository\CtReceptionRepository;
use App\Repository\CtConstAvDedRepository;
use App\Repository\CtConstAvDedCaracRepository;
use App\Repository\CtConstAvDedTypeRepository;
use App\Repository\CtVisiteRepository;
use App\Repository\CtVisiteExtraRepository;
use App\Repository\CtVisiteExtraTarifRepository;
use App\Repository\CtTypeDroitPTACRepository;
use App\Repository\CtAutreRepository;
use App\Repository\CtAutreVenteRepository;
use App\Repository\CtTypeReceptionRepository;
use App\Repository\CtDroitPTACRepository;
use App\Repository\CtCentreRepository;
use App\Repository\CtUtilisationRepository;
use App\Repository\CtVehiculeRepository;
use App\Repository\CtTypeVisiteRepository;
use App\Repository\CtUsageTarifRepository;
use App\Repository\CtUsageRepository;
use App\Repository\CtUserRepository;
use App\Entity\CtImprimeTech;
use App\Form\CtImprimeTechType;
use App\Repository\CtImprimeTechRepository;
use App\Entity\CtImprimeTechUse;
use App\Form\CtImprimeTechUseType;
use App\Form\CtImprimeTechUseDisableType;
use App\Form\CtImprimeTechUseMultipleType;
use App\Repository\CtImprimeTechUseRepository;
use App\Entity\CtBordereau;
use App\Form\CtBordereauType;
use App\Form\CtBordereauAjoutType;
use App\Repository\CtBordereauRepository;
use App\Controller\Datetime;
use App\Entity\CtCentre;
use App\Entity\CtTypeReception;
use App\Entity\CtConstAvDedCarac;
use App\Entity\CtGenreCategorie;
use App\Entity\CtVisite;
use App\Entity\CtMarque;
use App\Entity\CtUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * @Route("/ct_app_statistique")
 */
class CtAppSatistiqueController extends AbstractController
{
    //#[Route('/ct/app/satistique', name: 'app_ct_app_satistique')]
    /**
     * @Route("/", name="app_ct_app_statistique")
     */
    public function index(): Response
    {
        return $this->render('ct_app_statistique/index.html.twig', [
            'controller_name' => 'CtAppStatistiqueController',
        ]);
    }

    /**
     * @Route("/statistique_visite", name="app_ct_app_statistique_statistique_visite")
     */
    public function StatistiqueVisite(Request $request, CtUsageRepository $ctUsageRepository, CtCentreRepository $ctCentreRepository, CtVisiteRepository $ctVisiteRepository): Response
    {
        $centre = new CtCentre();
        $titre = "";
        $date_effective = "";
        $mois_effective = "";
        $trimeste_effective = "";
        $semestre_effective = "";
        $annee_effective = "";

        $mois_texte = [
            1 => "Janvier",
            2 => "Février",
            3 => "Mars",
            4 => "Avril",
            5 => "Mai",
            6 => "Juin",
            7 => "Juillet",
            8 => "Août",
            9 => "Septembre",
            10 => "Octobre",
            11 => "Novembre",
            12 => "Décembre",
        ];

        $trimestre_texte = [
            1 => "1er trimestre",
            2 => "2ème trimestre",
            3 => "3ème trimestre",
            4 => "4ème trimestre",
        ];

        $semestre_texte = [
            1 => "1er semestre",
            2 => "2ème semestre",
        ];

        if($request->request->get('form')){
            $rechercheform = $request->request->get('form');
            if(array_key_exists('mois', $rechercheform)){
                $mois_effective = $rechercheform['mois'];
            }
            if(array_key_exists('trimestre', $rechercheform)){
                $trimeste_effective = $rechercheform['trimestre'];
            }
            if(array_key_exists('semestre', $rechercheform)){
                $semestre_effective = $rechercheform['semestre'];
            }
            if(array_key_exists('annee', $rechercheform)){
                $annee_effective = $rechercheform['annee'];
            }
            $date_effective = $annee_effective;
            $titre = $date_effective;
            $centre = $this->getUser()->getCtCentreId();
            if($rechercheform['ct_centre_id'] != ""){
                $centre = $ctCentreRepository->findOneBy(["id" => $rechercheform['ct_centre_id']]);
            } else{
                $centre = $this->getUser()->getCtCentreId();
            }

            if($mois_effective != null){
                $date_effective = $annee_effective.'-'.$mois_effective;
                $titre = $mois_texte[$mois_effective].' '.$annee_effective;
            }
            if($trimeste_effective != null){
                switch($trimeste_effective){
                    case 1:
                        $date_effective = $annee_effective.'-01% OR c.vst_created LIKE %'.$annee_effective.'-02% OR c.vst_created LIKE %'.$annee_effective.'-03';
                        break;
                    
                    case 2:
                        $date_effective = $annee_effective.'-04% OR c.vst_created LIKE %'.$annee_effective.'-05% OR c.vst_created LIKE %'.$annee_effective.'-06';
                        break;
                            
                    case 3:
                        $date_effective = $annee_effective.'-07% OR c.vst_created LIKE %'.$annee_effective.'-08% OR c.vst_created LIKE %'.$annee_effective.'-09';
                        break;
                        
                    case 4:
                        $date_effective = $annee_effective.'-10% OR c.vst_created LIKE %'.$annee_effective.'-11% OR c.vst_created LIKE %'.$annee_effective.'-12';
                        break;
                        
                }
                $titre = $trimestre_texte[$trimeste_effective].' '.$annee_effective;
            }
            if($semestre_effective != null){
                $date_effective = $annee_effective.'-'.$semestre_effective;
                $titre = $semestre_texte[$semestre_effective].' '.$annee_effective;
            }

            //sur site
            $total_apte_sur_site = 0;
            $total_inapte_sur_site = 0;
            $total_total_payante_sur_site = 0;
            $total_gratuite_sur_site = 0;
            $total_total_sur_site = 0;
            $total_apte_itinerante = 0;
            $total_inapte_itinerante = 0;
            $total_total_payante_itinerante = 0;
            $total_gratuite_itinerante = 0;
            $total_total_itinerante = 0;
            $total_apte_domicile = 0;
            $total_inapte_domicile = 0;
            $total_total_payante_domicile = 0;
            $total_gratuite_domicile = 0;
            $total_total_domicile = 0;

            $liste_usages = $ctUsageRepository->findAll();
            $liste_statistique = new ArrayCollection();
            foreach($liste_usages as $lstu){
                $apte_sur_site = $ctVisiteRepository->findNombreVisitePayante($date_effective, [1], [1], [$centre->getId()], [$lstu->getId()], 2);
                $inapte_sur_site = $ctVisiteRepository->findNombreVisitePayante($date_effective, [1], [0], [$centre->getId()], [$lstu->getId()], 2);
                $total_payante_sur_site = $apte_sur_site + $inapte_sur_site;
                $gratuite_sur_site = $ctVisiteRepository->findNombreVisitePayante($date_effective, [1], [0, 1], [$centre->getId()], [$lstu->getId()], 1);
                $total_sur_site = $total_payante_sur_site + $gratuite_sur_site;

                $liste_centre_itinerante = $ctCentreRepository->findBy(["ctr_code" => $centre->getCtrCode()]);
                $lstctrit = new ArrayCollection();
                foreach($liste_centre_itinerante as $lstc){
                    if($centre->getId() != $lstc->getId()){
                        $lstctrit->add($lstc->getId());
                    }
                }
                $apte_itinerante = $ctVisiteRepository->findNombreVisitePayante($date_effective, [1], [1], $lstctrit, [$lstu->getId()], 2);
                $inapte_itinerante = $ctVisiteRepository->findNombreVisitePayante($date_effective, [1], [0], $lstctrit, [$lstu->getId()], 2);
                $total_payante_itinerante = $apte_itinerante + $inapte_itinerante;
                $gratuite_itinerante = $ctVisiteRepository->findNombreVisitePayante($date_effective, [1], [0, 1], $lstctrit, [$lstu->getId()], 1);
                $total_itinerante = $total_payante_itinerante + $gratuite_itinerante;

                $liste_centres = $ctCentreRepository->findBy(["ctr_code" => $centre->getCtrCode()]);
                $lstctrdom = new ArrayCollection();
                foreach($liste_centres as $lstc){
                    $lstctrdom->add($lstc->getId());
                }
                $apte_domicile = $ctVisiteRepository->findNombreVisitePayante($date_effective, [2], [1], $lstctrdom, [$lstu->getId()], 2);
                $inapte_domicile = $ctVisiteRepository->findNombreVisitePayante($date_effective, [2], [0], $lstctrdom, [$lstu->getId()], 2);
                $total_payante_domicile = $apte_domicile + $inapte_domicile;
                $gratuite_domicile = $ctVisiteRepository->findNombreVisitePayante($date_effective, [2], [0, 1], $lstctrdom, [$lstu->getId()], 1);
                $total_domicile = $total_payante_domicile + $gratuite_domicile;

                $total_apte_sur_site += $apte_sur_site;
                $total_inapte_sur_site += $inapte_sur_site;
                $total_total_payante_sur_site += $total_payante_sur_site;
                $total_gratuite_sur_site += $gratuite_sur_site;
                $total_total_sur_site += $total_sur_site;
                $total_apte_itinerante += $apte_itinerante;
                $total_inapte_itinerante += $inapte_itinerante;
                $total_total_payante_itinerante += $total_payante_itinerante;
                $total_gratuite_itinerante += $gratuite_itinerante;
                $total_total_itinerante += $total_itinerante;
                $total_apte_domicile += $apte_domicile;
                $total_inapte_domicile += $inapte_domicile;
                $total_total_payante_domicile += $total_payante_domicile;
                $total_gratuite_domicile += $gratuite_domicile;
                $total_total_domicile += $total_domicile;

                $statistique = [
                    'usage' => $lstu->getUsgLibelle(),
                    'apte_sur_site' => $apte_sur_site,
                    'inapte_sur_site' => $inapte_sur_site,
                    'total_payante_sur_site' => $total_payante_sur_site,
                    'gratuite_sur_site' => $gratuite_sur_site,
                    'total_sur_site' => $total_sur_site,
                    'apte_itinerante' => $apte_itinerante,
                    'inapte_itinerante' => $inapte_itinerante,
                    'total_payante_itinerante' => $total_payante_itinerante,
                    'gratuite_itinerante' => $gratuite_itinerante,
                    'total_itinerante' => $total_itinerante,
                    'apte_domicile' => $apte_domicile,
                    'inapte_domicile' => $inapte_domicile,
                    'total_payante_domicile' => $total_payante_domicile,
                    'gratuite_domicile' => $gratuite_domicile,
                    'total_domicile' => $total_domicile,
                ];
                $liste_statistique->add($statistique);
            }
            $pdfOptions = new Options();
            $pdfOptions->set('isRemoteEnabled', true);
            $pdfOptions->setIsRemoteEnabled(true);
            $pdfOptions->setIsPhpEnabled(true);
            $pdfOptions->set('defaultFont', 'Arial');
            $dompdf = new Dompdf($pdfOptions);
    
            $date = new \DateTime();
            $logo = file_get_contents($this->getParameter('logo').'logo.txt');
    
            $dossier = $this->getParameter('dossier_statistique_visite')."/".$centre->getCtrNom().'/'.$date->format('Y').'/'.$date->format('M').'/'.$date->format('d').'/';
            if (!file_exists($dossier)) {
                mkdir($dossier, 0777, true);
            }

            $html = $this->renderView('ct_app_statistique/imprime_statistique_visite.html.twig', [
                'logo' => $logo,
                'titre' => $titre,
                'province' => $centre->getCtProvinceId()->getPrvNom(),
                'centre' => $centre->getCtrNom(),
                'user' => $this->getUser(),
                'ct_visites'=> $liste_statistique,
                'total_apte_sur_site'=> $total_apte_sur_site,
                'total_inapte_sur_site'=> $total_inapte_sur_site,
                'total_total_payante_sur_site'=> $total_total_payante_sur_site,
                'total_gratuite_sur_site'=> $total_gratuite_sur_site,
                'total_total_sur_site'=> $total_total_sur_site,
                'total_apte_itinerante'=> $total_apte_itinerante,
                'total_inapte_itinerante'=> $total_inapte_itinerante,
                'total_total_payante_itinerante'=> $total_total_payante_itinerante,
                'total_gratuite_itinerante'=> $total_gratuite_itinerante,
                'total_total_itinerante'=> $total_total_itinerante,
                'total_apte_domicile'=> $total_apte_domicile,
                'total_inapte_domicile'=> $total_inapte_domicile,
                'total_total_payante_domicile'=> $total_total_payante_domicile,
                'total_gratuite_domicile'=> $total_gratuite_domicile,
                'total_total_domicile'=> $total_total_domicile ,
            ]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $output = $dompdf->output();
            $filename = "STATISTIQUE_VISITE_".$centre->getCtrNom().'_'.$date->format('Y_M_d_H_i_s').".pdf";
            file_put_contents($dossier.$filename, $output);
            $dompdf->stream("STATISTIQUE_VISITE_".$centre->getCtrNom().'_'.$date->format('Y_M_d_H_i_s').".pdf", [
                "Attachment" => true,
            ]);
        }

        $mois = [
            "Janvier" => 1,
            "Février" => 2,
            "Mars" => 3,
            "Avril" => 4,
            "Mai" => 5,
            "Juin" => 6,
            "Juillet" => 7,
            "Août" => 8,
            "Septembre" => 9,
            "Octobre" => 10,
            "Novembre" => 11,
            "Décembre" => 12,
        ];
        $trimestre = [
            "trimestre 1" => 1,
            "trimestre 2" => 2,
            "trimestre 3" => 3,
            "trimestre 4" => 4,
        ];
        $semestre = [
            "semestre 1" => 1,
            "semestre 2" => 2,
        ];
        $date = new \DateTime();
        $annee_max = intval($date->format('Y'));
        $liste_annee = [
            strval($annee_max) => $annee_max,
            strval($annee_max - 1) => $annee_max - 1,
            strval($annee_max - 2) => $annee_max - 2,
            strval($annee_max - 3) => $annee_max - 3,
            strval($annee_max - 4) => $annee_max - 4,
            strval($annee_max - 5) => $annee_max - 5,
            strval($annee_max - 6) => $annee_max - 6,
            strval($annee_max - 7) => $annee_max - 7,
            strval($annee_max - 8) => $annee_max - 8,
            strval($annee_max - 9) => $annee_max - 9,
            strval($annee_max - 10) => $annee_max - 10,
        ];
        $form_annuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_annuel->handleRequest($request);
        $form_semestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('semestre', ChoiceType::class, [
                'label' => 'Séléctionner le semestre',
                'choices' => $semestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_semestriel->handleRequest($request);
        $form_trimestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('trimestre', ChoiceType::class, [
                'label' => 'Séléctionner le trimestre',
                'choices' => $trimestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_trimestriel->handleRequest($request);
        $form_mensuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('mois', ChoiceType::class, [
                'label' => 'Séléctionner le mois',
                'choices' => $mois,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_mensuel->handleRequest($request);

        return $this->render('ct_app_statistique/statistique_visite.html.twig', [
            'form_annuel' => $form_annuel->createView(),
            'form_semestriel' => $form_semestriel->createView(),
            'form_trimestriel' => $form_trimestriel->createView(),
            'form_mensuel' => $form_mensuel->createView(),
        ]);
    }

    /**
     * @Route("/statistique_reception", name="app_ct_app_statistique_statistique_reception")
     */
    public function StatistiqueReception(Request $request): Response
    {
        $mois = [
            "Janvier" => 1,
            "Février" => 2,
            "Mars" => 3,
            "Avril" => 4,
            "Mai" => 5,
            "Juin" => 6,
            "Juillet" => 7,
            "Août" => 8,
            "Septembre" => 9,
            "Octobre" => 10,
            "Novembre" => 11,
            "Décembre" => 12,
        ];
        $trimestre = [
            "trimestre 1" => 1,
            "trimestre 2" => 2,
            "trimestre 3" => 3,
            "trimestre 4" => 4,
        ];
        $semestre = [
            "semestre 1" => 1,
            "semestre 2" => 2,
        ];
        $date = new \DateTime();
        $annee_max = intval($date->format('Y'));
        $liste_annee = [
            strval($annee_max) => $annee_max,
            strval($annee_max - 1) => $annee_max - 1,
            strval($annee_max - 2) => $annee_max - 2,
            strval($annee_max - 3) => $annee_max - 3,
            strval($annee_max - 4) => $annee_max - 4,
            strval($annee_max - 5) => $annee_max - 5,
            strval($annee_max - 6) => $annee_max - 6,
            strval($annee_max - 7) => $annee_max - 7,
            strval($annee_max - 8) => $annee_max - 8,
            strval($annee_max - 9) => $annee_max - 9,
            strval($annee_max - 10) => $annee_max - 10,
        ];
        $form_annuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_annuel->handleRequest($request);
        $form_semestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('semestre', ChoiceType::class, [
                'label' => 'Séléctionner le semestre',
                'choices' => $semestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_semestriel->handleRequest($request);
        $form_trimestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('trimestre', ChoiceType::class, [
                'label' => 'Séléctionner le trimestre',
                'choices' => $trimestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_trimestriel->handleRequest($request);
        $form_mensuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('mois', ChoiceType::class, [
                'label' => 'Séléctionner le mois',
                'choices' => $mois,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_mensuel->handleRequest($request);

        return $this->render('ct_app_statistique/statistique_reception.html.twig', [
            'form_annuel' => $form_annuel->createView(),
            'form_semestriel' => $form_semestriel->createView(),
            'form_trimestriel' => $form_trimestriel->createView(),
            'form_mensuel' => $form_mensuel->createView(),
        ]);
    }

    /**
     * @Route("/statistique_constatation", name="app_ct_app_statistique_statistique_constatation")
     */
    public function StatistiqueConstatation(Request $request): Response
    {
        $mois = [
            "Janvier" => 1,
            "Février" => 2,
            "Mars" => 3,
            "Avril" => 4,
            "Mai" => 5,
            "Juin" => 6,
            "Juillet" => 7,
            "Août" => 8,
            "Septembre" => 9,
            "Octobre" => 10,
            "Novembre" => 11,
            "Décembre" => 12,
        ];
        $trimestre = [
            "trimestre 1" => 1,
            "trimestre 2" => 2,
            "trimestre 3" => 3,
            "trimestre 4" => 4,
        ];
        $semestre = [
            "semestre 1" => 1,
            "semestre 2" => 2,
        ];
        $date = new \DateTime();
        $annee_max = intval($date->format('Y'));
        $liste_annee = [
            strval($annee_max) => $annee_max,
            strval($annee_max - 1) => $annee_max - 1,
            strval($annee_max - 2) => $annee_max - 2,
            strval($annee_max - 3) => $annee_max - 3,
            strval($annee_max - 4) => $annee_max - 4,
            strval($annee_max - 5) => $annee_max - 5,
            strval($annee_max - 6) => $annee_max - 6,
            strval($annee_max - 7) => $annee_max - 7,
            strval($annee_max - 8) => $annee_max - 8,
            strval($annee_max - 9) => $annee_max - 9,
            strval($annee_max - 10) => $annee_max - 10,
        ];
        $form_annuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_annuel->handleRequest($request);
        $form_semestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('semestre', ChoiceType::class, [
                'label' => 'Séléctionner le semestre',
                'choices' => $semestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_semestriel->handleRequest($request);
        $form_trimestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('trimestre', ChoiceType::class, [
                'label' => 'Séléctionner le trimestre',
                'choices' => $trimestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_trimestriel->handleRequest($request);
        $form_mensuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('mois', ChoiceType::class, [
                'label' => 'Séléctionner le mois',
                'choices' => $mois,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_mensuel->handleRequest($request);

        return $this->render('ct_app_statistique/statistique_constatation.html.twig', [
            'form_annuel' => $form_annuel->createView(),
            'form_semestriel' => $form_semestriel->createView(),
            'form_trimestriel' => $form_trimestriel->createView(),
            'form_mensuel' => $form_mensuel->createView(),
        ]);
    }

    /**
     * @Route("/statistique_imprime_technique", name="app_ct_app_statistique_statistique_imprime_technique")
     */
    public function StatistiqueImprimeTechnique(Request $request): Response
    {
        $mois = [
            "Janvier" => 1,
            "Février" => 2,
            "Mars" => 3,
            "Avril" => 4,
            "Mai" => 5,
            "Juin" => 6,
            "Juillet" => 7,
            "Août" => 8,
            "Septembre" => 9,
            "Octobre" => 10,
            "Novembre" => 11,
            "Décembre" => 12,
        ];
        $trimestre = [
            "trimestre 1" => 1,
            "trimestre 2" => 2,
            "trimestre 3" => 3,
            "trimestre 4" => 4,
        ];
        $semestre = [
            "semestre 1" => 1,
            "semestre 2" => 2,
        ];
        $date = new \DateTime();
        $annee_max = intval($date->format('Y'));
        $liste_annee = [
            strval($annee_max) => $annee_max,
            strval($annee_max - 1) => $annee_max - 1,
            strval($annee_max - 2) => $annee_max - 2,
            strval($annee_max - 3) => $annee_max - 3,
            strval($annee_max - 4) => $annee_max - 4,
            strval($annee_max - 5) => $annee_max - 5,
            strval($annee_max - 6) => $annee_max - 6,
            strval($annee_max - 7) => $annee_max - 7,
            strval($annee_max - 8) => $annee_max - 8,
            strval($annee_max - 9) => $annee_max - 9,
            strval($annee_max - 10) => $annee_max - 10,
        ];
        $form_annuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_annuel->handleRequest($request);
        $form_semestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('semestre', ChoiceType::class, [
                'label' => 'Séléctionner le semestre',
                'choices' => $semestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_semestriel->handleRequest($request);
        $form_trimestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('trimestre', ChoiceType::class, [
                'label' => 'Séléctionner le trimestre',
                'choices' => $trimestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_trimestriel->handleRequest($request);
        $form_mensuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('mois', ChoiceType::class, [
                'label' => 'Séléctionner le mois',
                'choices' => $mois,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_mensuel->handleRequest($request);

        return $this->render('ct_app_statistique/statistique_imprimer.html.twig', [
            'form_annuel' => $form_annuel->createView(),
            'form_semestriel' => $form_semestriel->createView(),
            'form_trimestriel' => $form_trimestriel->createView(),
            'form_mensuel' => $form_mensuel->createView(),
        ]);
    }

    /**
     * @Route("/statistique_autre_service", name="app_ct_app_statistique_statistique_autre_service")
     */
    public function StatistiqueAutreService(Request $request): Response
    {
        $mois = [
            "Janvier" => 1,
            "Février" => 2,
            "Mars" => 3,
            "Avril" => 4,
            "Mai" => 5,
            "Juin" => 6,
            "Juillet" => 7,
            "Août" => 8,
            "Septembre" => 9,
            "Octobre" => 10,
            "Novembre" => 11,
            "Décembre" => 12,
        ];
        $trimestre = [
            "trimestre 1" => 1,
            "trimestre 2" => 2,
            "trimestre 3" => 3,
            "trimestre 4" => 4,
        ];
        $semestre = [
            "semestre 1" => 1,
            "semestre 2" => 2,
        ];
        $date = new \DateTime();
        $annee_max = intval($date->format('Y'));
        $liste_annee = [
            strval($annee_max) => $annee_max,
            strval($annee_max - 1) => $annee_max - 1,
            strval($annee_max - 2) => $annee_max - 2,
            strval($annee_max - 3) => $annee_max - 3,
            strval($annee_max - 4) => $annee_max - 4,
            strval($annee_max - 5) => $annee_max - 5,
            strval($annee_max - 6) => $annee_max - 6,
            strval($annee_max - 7) => $annee_max - 7,
            strval($annee_max - 8) => $annee_max - 8,
            strval($annee_max - 9) => $annee_max - 9,
            strval($annee_max - 10) => $annee_max - 10,
        ];
        $form_annuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_annuel->handleRequest($request);
        $form_semestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('semestre', ChoiceType::class, [
                'label' => 'Séléctionner le semestre',
                'choices' => $semestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_semestriel->handleRequest($request);
        $form_trimestriel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('trimestre', ChoiceType::class, [
                'label' => 'Séléctionner le trimestre',
                'choices' => $trimestre,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_trimestriel->handleRequest($request);
        $form_mensuel = $this->createFormBuilder()
            ->add('annee', ChoiceType::class, [
                'label' => 'Séléctionner l\'année',
                'choices' => $liste_annee,
                'data' => $annee_max,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('mois', ChoiceType::class, [
                'label' => 'Séléctionner le mois',
                'choices' => $mois,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->add('ct_centre_id', EntityType::class, [
                'label' => 'Séléctionner le centre',
                'class' => CtCentre::class,
                'multiple' => false,
                'attr' => [
                    'class' => 'multi',
                    'multiple' => false,
                    'style' => 'width:100%;',
                    'data-live-search' => true,
                    'data-select' => true,
                ],
            ])
            ->getForm();
        $form_mensuel->handleRequest($request);

        return $this->render('ct_app_statistique/statistique_autre_service.html.twig', [
            'form_annuel' => $form_annuel->createView(),
            'form_semestriel' => $form_semestriel->createView(),
            'form_trimestriel' => $form_trimestriel->createView(),
            'form_mensuel' => $form_mensuel->createView(),
        ]);
    }
}
