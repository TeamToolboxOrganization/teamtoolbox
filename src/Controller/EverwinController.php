<?php

namespace App\Controller;

use App\Entity\Office;
use App\Entity\User;
use App\Entity\Vacation;
use App\Form\EverwinType;
use App\Repository\OfficeRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Security\CSPDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * Controller used to manage current user.
 *
 * @Route("/import")
 *
 */
#[IsGranted('ROLE_ADMIN')]
#[Route("/import")]
class EverwinController extends AbstractController
{

    #[Route("/everwin",name: "upload_everwin")]
    public function upload(Request $request, SluggerInterface $slugger, ManagerRegistry $managerRegistry, UserRepository $users, VacationRepository $vacations, OfficeRepository $officeRepository): Response
    {
        $form = $this->createForm(EverwinType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $extractEverwin */
            $extractEverwin = $form->get('extractEverwin')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($extractEverwin) {
                $originalFilename = pathinfo($extractEverwin->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.xlsx';

                // Move the file to the directory where brochures are stored
                try {
                    $extractEverwin->move(
                        $this->getParameter('everwin_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }

            $managerRegistry->getManager()->flush();

            $this->importEverwinData($managerRegistry, $vacations, $officeRepository, $users, $newFilename);

            if ($extractEverwin) {
                $filesystem = new Filesystem();
                $filesystem->remove($this->getParameter('everwin_directory') . '/' . $newFilename);
            }

            $this->addFlash('success', "Mise à jour des absences réussie");

            return $this->redirectToRoute('upload_everwin');
        }

        return new Response(
            $this->renderView('default/upload.html.twig', [
                'form' => $form->createView(),
            ]),
            Response::HTTP_OK,
            [
                'Content-Security-Policy' => CSPDefinition::defaultRules
            ]
        );
    }

    public function importEverwinData(ManagerRegistry $managerRegistry, VacationRepository $vacations, OfficeRepository $officeRepository, UserRepository $users, string $fileName): Response
    {
        $filePath = $this->getParameter('everwin_directory') . '/' . $fileName;

        //$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $encoding = \PhpOffice\PhpSpreadsheet\Reader\Csv::guessEncoding($filePath);
        $reader->setInputEncoding($encoding);
        $reader->setReadDataOnly(true);
        $reader->setDelimiter(';');
        $reader->setEnclosure('"');

        $vacations->truncateDatesForManager($this->getUser());
        $officeRepository->truncateDatesForManager($this->getUser());

        /**
         * @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
         */
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Get the highest row and column numbers referenced in the worksheet
        $highestRow = $worksheet->getHighestRow(); // e.g. 10

        $allUsers = $users->findAll();
        $userMapping = [];
        foreach ($allUsers as $user){
            $userMapping[$this->reverseAndUpperName($user->getFullName())] = $user;
        }

        for ($row = 2; $row <= $highestRow; ++$row) {

            // Get collab Id
            $collabName = mb_strtoupper($worksheet->getCellByColumnAndRow(4, $row)->getValue());
            if(array_key_exists($collabName, $userMapping) ){
                $collab = $userMapping[$collabName];
            } else {
                continue;
            }

            $type = $worksheet->getCellByColumnAndRow(11, $row)->getValue();

            $startDate = $this->convertRHPIDate($worksheet->getCellByColumnAndRow(6, $row)->getValue(), $worksheet->getCellByColumnAndRow(7, $row)->getValue(), true);
            $endDate = $this->convertRHPIDate($worksheet->getCellByColumnAndRow(8, $row)->getValue(), $worksheet->getCellByColumnAndRow(9, $row)->getValue(), false);

            $em = $managerRegistry->getManager();

            if($type == "Télétravail"){
                $office = new Office();
                $office->setType($type);
                $office->setStartAt($startDate);
                $office->setEndAt($endDate);
                $office->setCollab($collab);
                $office->setImportFromRhpi(1);
                $em->persist($office);
            } else {
                $vacation = new Vacation();
                $vacation->setType($type);
                $vacation->setCollab($collab);

                $vacation->setStartAt($startDate);
                $vacation->setEndAt($endDate);
                $vacation->setState($worksheet->getCellByColumnAndRow(5, $row)->getValue());
                $vacation->setValue((int)$worksheet->getCellByColumnAndRow(12, $row)->getValue());

                $em->persist($vacation);
            }
            $em->flush();
        }

        return new JsonResponse(["action" => "updated"], Response::HTTP_OK);
    }

    private function reverseAndUpperName(string $name){
        $explodeName = explode(' ', $name);
        $reverseName = $explodeName;
        if(sizeof($explodeName) == 1){
            $reverseName = $explodeName[0];
        } else {
            $reverseName = $explodeName[1] . " " . $explodeName[0];
        }
        return mb_strtoupper($reverseName);
    }

    private function convertRHPIDate($excelDateValue, $excelTimeValue, $isStart){
        if($excelTimeValue == "M"){
            $result = $excelDateValue . " 12:00";
        } elseif($excelTimeValue == "A"){
            $result = $excelDateValue . " 23:59";
        } else {
            if ($isStart){
                $result = $excelDateValue . " 00:00";
            }else{
                $result = $excelDateValue . " 23:59";
            }
        }
        return \DateTime::createFromFormat('d/m/Y H:i', $result);
    }

}
