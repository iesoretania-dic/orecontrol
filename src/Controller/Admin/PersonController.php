<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Service\CsvReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/personas')]
#[IsGranted('ROLE_ADMIN')]
class PersonController extends AbstractController
{
    private const CSV_USERNAME_COLUMN = 'Usuario IdEA';
    private const CSV_NAME_COLUMN = 'Empleado/a';

    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CsvReader $csvReader,
    ) {
    }

    #[Route('', name: 'admin_person_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/person/index.html.twig', [
            'persons' => $this->personRepository->findBy([], ['username' => 'ASC']),
        ]);
    }

    #[Route('/importar', name: 'admin_person_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('admin/person/import.html.twig');
        }

        if (!$this->isCsrfTokenValid('import_persons', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $file = $request->files->get('csv');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $this->addFlash('error', 'Selecciona un fichero CSV válido.');

            return $this->redirectToRoute('admin_person_import');
        }

        $parsed = $this->csvReader->parse((string) file_get_contents($file->getPathname()));

        if ($parsed['headers'] === []) {
            $this->addFlash('error', 'El fichero está vacío o no se ha podido leer.');

            return $this->redirectToRoute('admin_person_import');
        }

        $missing = $this->csvReader->findMissingColumn($parsed['headers'], [self::CSV_USERNAME_COLUMN]);
        if ($missing !== null) {
            $this->addFlash('error', sprintf('Falta la columna «%s» en el fichero.', $missing));

            return $this->redirectToRoute('admin_person_import');
        }

        $created = 0;
        $existing = 0;
        $skipped = 0;

        foreach ($parsed['rows'] as $row) {
            $username = $row[self::CSV_USERNAME_COLUMN] ?? '';
            if ($username === '') {
                $skipped++;
                continue;
            }

            $person = $this->personRepository->findOneBy(['username' => $username]);
            if ($person !== null) {
                $existing++;
                continue;
            }

            $person = new Person();
            $person->setUsername($username);
            $person->setDisplayName($this->parseDisplayName($row[self::CSV_NAME_COLUMN] ?? ''));
            $person->setLevel(0);
            $person->setManager(false);
            $person->setActive(true);
            // Imported teachers are new to the app by definition: they authenticate
            // against iSéneca, never with a local password we'd have to invent.
            $person->setExternal(true);
            $person->setPassword(null);

            $this->personRepository->save($person);
            $created++;
        }

        $this->personRepository->flush();

        $this->addFlash('success', sprintf(
            '%d docentes importados, %d ya existían, %d filas omitidas.',
            $created,
            $existing,
            $skipped
        ));

        return $this->redirectToRoute('admin_person_index');
    }

    /** Turns "Apellidos, Nombre" (as exported by iSéneca) into "Nombre Apellidos". */
    private function parseDisplayName(string $fullName): ?string
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return null;
        }

        $parts = explode(',', $fullName, 2);
        if (count($parts) !== 2) {
            return $fullName;
        }

        $lastName = trim($parts[0]);
        $firstName = trim($parts[1]);

        return trim($firstName . ' ' . $lastName);
    }

    #[Route('/nueva', name: 'admin_person_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new Person());
    }

    #[Route('/{id}/editar', name: 'admin_person_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person): Response
    {
        return $this->handleForm($request, $person);
    }

    #[IsCsrfTokenValid('toggle_person', tokenKey: '_token')]
    #[Route('/{id}/activar', name: 'admin_person_activate', methods: ['POST'])]
    public function activate(Person $person): Response
    {
        $person->setActive(true);
        $this->personRepository->save($person, true);
        $this->addFlash('success', sprintf('"%s" activado.', $person->getUsername()));

        return $this->redirectToRoute('admin_person_index');
    }

    #[IsCsrfTokenValid('toggle_person', tokenKey: '_token')]
    #[Route('/{id}/desactivar', name: 'admin_person_deactivate', methods: ['POST'])]
    public function deactivate(Person $person): Response
    {
        $person->setActive(false);
        $this->personRepository->save($person, true);
        $this->addFlash('success', sprintf('"%s" desactivado.', $person->getUsername()));

        return $this->redirectToRoute('admin_person_index');
    }

    private function handleForm(Request $request, Person $person): Response
    {
        $isNew = $person->getId() === null;
        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('person_form', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $username = trim((string) $request->request->get('username', ''));
            $displayName = trim((string) $request->request->get('display_name', ''));
            $level = (int) $request->request->get('level', 0);
            $manager = $request->request->getBoolean('manager');
            $external = $request->request->getBoolean('external');
            $active = $request->request->getBoolean('active', true);
            $plainPassword = (string) $request->request->get('password', '');

            if ($username === '') {
                $errors[] = 'El usuario no puede estar vacío.';
            } else {
                $existing = $this->personRepository->findOneBy(['username' => $username]);
                if ($existing !== null && $existing->getId() !== $person->getId()) {
                    $errors[] = 'Ya existe un docente con ese usuario.';
                }
            }

            if (!$external && $isNew && $plainPassword === '') {
                $errors[] = 'La contraseña es obligatoria para un usuario local.';
            }

            if ($errors === []) {
                $person->setUsername($username);
                $person->setDisplayName($displayName !== '' ? $displayName : null);
                $person->setLevel($level);
                $person->setManager($manager);
                $person->setExternal($external);
                $person->setActive($active);

                if ($external) {
                    $person->setPassword(null);
                } elseif ($plainPassword !== '') {
                    $person->setPassword($this->passwordHasher->hashPassword($person, $plainPassword));
                }

                $this->personRepository->save($person, true);
                $this->addFlash('success', sprintf('Docente "%s" guardado.', $person->getUsername()));

                return $this->redirectToRoute('admin_person_index');
            }
        }

        return $this->render('admin/person/form.html.twig', [
            'person' => $person,
            'is_new' => $isNew,
            'errors' => $errors,
        ]);
    }
}
