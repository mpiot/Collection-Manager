<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plasmid;
use AppBundle\Form\PlasmidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class plasmidController
 * @package AppBundle\Controller
 * 
 * @Route("/plasmid")
 */
class PlasmidController extends Controller
{
    /**
     * @Route("/", name="plasmid_index")
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $plasmids = $em->getRepository('AppBundle:Plasmid')->findAll();

        return $this->render('plasmid/index.html.twig', array(
            'plasmids' => $plasmids,
        ));
    }

    /**
     * @Route("/view/{id}", name="plasmid_view")
     */
    public function viewAction(Plasmid $plasmid)
    {
        // Get the plasmid file
        if (null !== $plasmid->getGenBankFile()) {
            $finder = new Finder();
            $finder->in('files/genBankFiles')->files()->name($plasmid->getGenBankFile()->getPath());


            foreach ($finder as $file) {
                $gbkFile = $file->getContents();
            }

            function Gbk2Array($file) {
                $lines = explode("\n", $file);
                $array = [];
                //$previousCat = null;
                //$arraySwitched = false;

                $previousFeature = null;
                $i = 0;

                foreach ($lines as $line) {
                    // First we want Features data, they starts by 21 spaces
                    if (preg_match('/^ {5}([\w]+) +(?:(complement)\()?(\d+)..(\d+)\)?/', $line, $matches)) {
                        $previousFeature = $i++;

                        // The feature type: source, misc_feature, promoter, ...
                        $array['features'][]['type'] = $matches[1];

                        // The position: an array like [sens/reverse, start, end]
                        $array['features'][$i-1]['position'] = [
                            'sens' => ('complement' === $matches[2]) ? 'reverse' : 'sens',
                            'start' => $matches[3],
                            'end' => $matches[4],
                        ];

                        if ('complement' === $matches[2]) {
                            $array['features'][$i-1]['position'] = [
                                'start' => $matches[4],
                                'end' => $matches[3],
                            ];
                        } else {
                            $array['features'][$i-1]['position'] = [
                                'start' => $matches[3],
                                'end' => $matches[4],
                            ];
                        }
                    }

                    // In second, we want other informations on features (organism, mol_type, label, gene, translation)
                    // all of this depend of the feature type
                    // If it's all but no a translation
                    elseif (preg_match('/^ {21}\/([a-zA-Z_]+)=(?:"([\w\d _.\-\(\)]+)"|(?:(\d)))/', $line, $matches)) {
                        if('codon_start' === $matches[1]) {
                            $array['features'][$i-1]['codon_start'] = $matches[3];
                        } elseif ('note' === $matches[1]) {
                            $array['features'][$i-1]['note'][] = $matches[2];
                        }
                        else {
                            $array['features'][$i-1][$matches[1]] = $matches[2];
                        }
                    }

                    // If it's a translation
                    elseif (preg_match('/ {21}(?:\/translation=")?([A-Z]+)/', $line, $matches)) {
                        if (array_key_exists('translation', $array['features'][$i-1])) {
                            $array['features'][$i-1]['translation'] .= $matches[1];
                        } else {
                            $array['features'][$i-1]['translation'] = $matches[1];
                        }
                    }

                    // Finally, if it the sequence
                    elseif (preg_match('/^ +\d+ ([\w ]+)/', $line, $matches)) {
                        $fasta = preg_replace('/ |\d|\n/', '', $matches[1]);

                        if (array_key_exists('fasta', $array)) {
                            $array['fasta'] .= $fasta;
                        } else {
                            $array['fasta'] = $fasta;
                        }
                    }
                }

                return $array;
            }

            $gbkArray = Gbk2Array($gbkFile);
        } else {
            $gbkFile = null;
            $gbkArray = null;
        }

        return $this->render('plasmid/view.html.twig', array(
            'plasmid' => $plasmid,
            'gbkFile' => $gbkFile,
            'gbk' => $gbkArray,
        ));
    }

    /**
     * @Route("/add", name="plasmid_add")
     */
    public function addAction(Request $request)
    {
        $plasmid = new Plasmid();
        $form = $this->createForm(PlasmidType::class, $plasmid);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ((0 === $form->get('addGenBankFile')->getData()) && (null !== $plasmid->getGenBankFile())) {
                $em->persist($plasmid->getGenBankFile());
            }

            $em->persist($plasmid);
            $em->flush();

            $this->addFlash('success', 'The plasmid has been added successfully.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/edit/{id}", name="plasmid_edit")
     */
    public function editAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createForm(PlasmidType::class, $plasmid);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ((0 === $form->get('addGenBankFile')->getData()) && (null !== $plasmid->getGenBankFile())) {
                $em->remove($plasmid->getGenBankFile());
                $plasmid->setGenBankFile(null);
            }

            $em->flush();

            $this->addFlash('success', 'The plasmid has been successfully edited.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/edit.html.twig', array(
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/delete/{id}", name="plasmid_delete")
     */
    public function deleteAction(Plasmid $plasmid, Request $request)
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($plasmid);
            $em->flush();

            $this->addFlash('success', 'The plasmid has been deleted successfully.');

            return $this->redirectToRoute('plasmid_index');
        }

        return $this->render('plasmid/delete.html.twig', array(
            'plasmid' => $plasmid,
            'form' => $form->createView(),
        ));
    }
}
