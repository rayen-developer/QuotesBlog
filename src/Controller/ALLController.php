<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class ALLController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     * @param $request
     */
    public function index(Request $request): Response
    {
        if (!isset($_SESSION)) {
            session_start();
        }


        $repository = $this->getDoctrine()->getRepository(Auteur::class);
        $auteurs = $repository->findALL();

        if ($request->isXmlHttpRequest()) {

            if ($request->request->get('radioValue') == null) {
                $val = $request->query->get("searchValue");
                $val1 = $request->query->get("radioValue");
            } else {
                $val = $request->request->get("searchValue");
                $val1 = $request->request->get("radioValue");
            }


            $jsonData = array();
            $idx = 0;
            if ($val1 == '0') {
                dump("here");
                $jsonData = array();
                foreach ($auteurs as $auteur) {
                    if (str_contains(strtoupper($auteur->getCitation()), strtoupper(trim($val)))) {
                        $temp = array(
                            'name' => $auteur->getCitation()

                        );
                        $jsonData[$idx++] = $temp;
                    }
                }
            } else if ($val1 == "1") {
                $jsonData = array();
                foreach ($auteurs as $auteur) {
                    if (str_contains(strtoupper($auteur->getSiecle()), strtoupper(trim($val)))) {
                        $temp = array(
                            'name' => $auteur->getSiecle()

                        );
                        if (!in_array($temp, $jsonData)) {
                            $jsonData[$idx++] = $temp;
                        }
                    }
                }
            } else {
                foreach ($auteurs as $auteur) {
                    $str = $auteur->getNom() . " " . $auteur->getPrenom();
                    if (str_contains(strtoupper($str), strtoupper(trim($val)))) {
                        $temp = array(
                            'name' => $str

                        );
                        $jsonData[$idx++] = $temp;
                    }
                }
            }





            return new JsonResponse($jsonData);
        }
        $cit = array();
        if (count($auteurs) > 5) {
            $rand_keys = array_rand($auteurs, 5);
            for ($i = 0; $i <= 4; $i++) {
                $cit[$i] = $auteurs[$rand_keys[$i]];
            }
        } else {
            $cit = $auteurs;
        }
        dump(empty($_SESSION['user']));


        return $this->render('all/index.html.twig', [
            "auteurs" => $cit,
            "logout" => empty($_SESSION['user'])

        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */

    public function logout(Request $request)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        session_unset();
        session_destroy();

        return $this->redirectToRoute('home', ['logout' => empty($_SESSION['user'])]);
    }

    /**
     * @Route("/login", name="login")
     */

    public function login(Request $request)
    {
        if (!isset($_SESSION)) {
            session_start();
        }




        if ($request->isMethod("POST")) {

            $repository = $this->getDoctrine()->getRepository(User::class);

            $user = $repository->findBy(array('email' => $request->request->get("email")));
            if ($user != []) {
                if ($user[0]->getPassword() == $request->request->get("password")) {
                    $_SESSION["user"] = "user";



                    return $this->redirectToRoute('home', ['logout' => empty($_SESSION['user'])]);
                } else {
                    return $this->render('all/login.html.twig', [
                        "test" => true,
                        "logout" => empty($_SESSION['user'])


                    ]);
                }
            } else {
                return $this->render('all/login.html.twig', [
                    "test" => true,
                    "logout" => empty($_SESSION['user'])


                ]);
            }
        }
        return $this->render('all/login.html.twig', [
            "test" => false,
            "logout" => empty($_SESSION['user'])
        ]);
    }
    /**
     * @Route("/inscription", name="inscription")
     */

    public function inscription(Request $request)
    {
        if (!isset($_SESSION)) {
            session_start();
        }


        if ($request->isMethod("POST")) {

            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->findBy(array('email' => $request->request->get("email")));
            if ($user == []) {

                $user = new User();
                $user->setEmail($request->request->get('email'))
                    ->setPassword($request->request->get('password'));
                dump($user);


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('login', ["logout" => empty($_SESSION['user'])]);
            } else {
                return $this->render('all/inscription.html.twig', [
                    "test" => true,
                    "logout" => empty($_SESSION['user'])
                ]);
            }
        }








        return $this->render('all/inscription.html.twig', [
            "test" => false,
            "logout" => empty($_SESSION['user'])
        ]);
    }

    /**
     * @Route("/insertion", name="insertion")
     */
    public function insertion(Request $request): Response
    {

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!empty($_SESSION['user'])) {

            if ($request->isMethod("POST")) {
                $auteur = new Auteur();
                $auteur->setNom($request->request->get('nom'))
                    ->setPrenom($request->request->get('prenom'))
                    ->setCitation($request->request->get('citation'))
                    ->setSiecle($request->request->get('siecle'));
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($auteur);

                $entityManager->flush();

                dump($auteur);
            }

            return $this->render('all/insertion.html.twig', [

                "logout" => empty($_SESSION['user'])
            ]);
        } else {
            return $this->redirectToRoute('login', ["logout" => empty($_SESSION['user'])]);
        }
    }
    /**
     * @Route("/afficher", name="afficher")
     */
    public function afficher(Request $request): Response
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $repository = $this->getDoctrine()->getRepository(Auteur::class);
        $auteurs = $repository->findALL();

        $bool = false;


        $val = $request->request->get("search");
        $val1 = $request->request->get("select");
        dump($val);
        dump($val1);
        $jsonData = array();
        if ($val != '') {
            $idx = 0;
            if ($val1 == '0') {
                $jsonData = array();
                foreach ($auteurs as $auteur) {
                    if (str_contains(strtoupper($auteur->getCitation()), strtoupper(trim($val)))) {
                        $temp = $auteur;

                        $jsonData[$idx++] = $temp;
                    }
                }
            } else if ($val1 == "1") {
                $jsonData = array();
                foreach ($auteurs as $auteur) {
                    if (str_contains(strtoupper($auteur->getSiecle()), strtoupper(trim($val)))) {
                        $temp = $auteur;
                        $jsonData[$idx++] = $temp;
                    }
                }
            } else {
                foreach ($auteurs as $auteur) {
                    $str = $auteur->getNom() . " " . $auteur->getPrenom();
                    if (str_contains(strtoupper($str), strtoupper(trim($val)))) {
                        $temp = $auteur;
                        $jsonData[$idx++] = $temp;
                    }
                }
            }
        }
        if (count($jsonData) != 0) {
            $bool = true;
        }
        if ($request->isMethod("GET")) {

            return $this->render('all/affichecit.html.twig', [

                "auteurs" => array(),
                "bool" => false,
                "logout" => empty($_SESSION['user'])

            ]);
        }
        if ($request->isMethod("POST")) {
            dump($request);
            $x = $request->request->get('radioSelected');
            if ($x != null) {
                $y = $request->request->get('textSelected');
                if ($x == '0') {
                    $repository = $this->getDoctrine()->getRepository(Auteur::class);
                    $auteur = $repository->findBy(array('citation' => $y));
                    dump($auteur);
                    return $this->render('all/affichecit.html.twig', [

                        "auteurs" => $auteur,
                        "bool" => true,
                        "logout" => empty($_SESSION['user'])


                    ]);
                }
                if ($x == '1') {
                    $repository = $this->getDoctrine()->getRepository(Auteur::class);
                    $auteur = $repository->findBy(array('siecle' => $y));
                    return $this->render('all/affichecit.html.twig', [

                        "auteurs" => $auteur,
                        "bool" => true,
                        "logout" => empty($_SESSION['user'])


                    ]);
                } else {
                    $repository = $this->getDoctrine()->getRepository(Auteur::class);
                    $auteurs = $repository->findALL();
                    $idx = 0;

                    $jsonData = array();
                    foreach ($auteurs as $auteur) {
                        $str = $auteur->getNom() . " " . $auteur->getPrenom();

                        if (strtoupper($str) == strtoupper($y)) {
                            $temp = $auteur;
                            $jsonData[$idx++] = $temp;
                        }
                    }
                    $auteur = $jsonData;
                    return $this->render('all/affichecit.html.twig', [

                        "auteurs" => $auteur,
                        "bool" => true,
                        "logout" => empty($_SESSION['user'])


                    ]);
                }
            } else {
                $y = $request->request->get("search");
                $x = $request->request->get("select");
                if ($y == '') {
                    return $this->render('all/affichecit.html.twig', [

                        "auteurs" => null,
                        "bool" => false,
                        "logout" => empty($_SESSION['user'])


                    ]);
                } else {
                    if ($x == '0') {
                        $repository = $this->getDoctrine()->getRepository(Auteur::class);
                        $auteurs = $repository->findAll();
                        $bool = true;
                        $jsonData = array();
                        foreach ($auteurs as $auteur) {
                            if (str_contains(strtoupper($auteur->getCitation()), strtoupper(trim($val)))) {

                                $jsonData[$idx++] = $auteur;
                            }
                        }
                        if (count($jsonData) == 0) {
                            $bool = false;
                        }
                        return $this->render('all/affichecit.html.twig', [

                            "auteurs" => $jsonData,
                            "bool" => $bool,
                            "logout" => empty($_SESSION['user'])


                        ]);
                    } else if ($x == "1") {
                        $jsonData = array();
                        $bool = true;
                        foreach ($auteurs as $auteur) {
                            if (str_contains(strtoupper($auteur->getSiecle()), strtoupper(trim($val)))) {


                                $jsonData[$idx++] = $auteur;
                            }
                        }
                        if (count($jsonData) == 0) {
                            $bool = false;
                        }
                        return $this->render('all/affichecit.html.twig', [

                            "auteurs" => $jsonData,
                            "bool" => $bool,
                            "logout" => empty($_SESSION['user'])


                        ]);
                    } else {
                        $bool = true;
                        $jsonData = array();
                        foreach ($auteurs as $auteur) {
                            $str = $auteur->getNom() . " " . $auteur->getPrenom();
                            if (str_contains(strtoupper($str), strtoupper(trim($val)))) {

                                $jsonData[$idx++] = $auteur;
                            }
                        }
                        if (count($jsonData) == 0) {
                            $bool = false;
                        }
                        return $this->render('all/affichecit.html.twig', [

                            "auteurs" => $jsonData,
                            "bool" => $bool,
                            "logout" => empty($_SESSION['user'])


                        ]);
                    }
                }
            }
        }
    }
}
