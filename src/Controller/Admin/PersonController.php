<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'admin_person_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/person/index.html.twig', [
            'persons' => $this->personRepository->findBy([], ['username' => 'ASC']),
        ]);
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
