<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Network;
use App\Repository\NetworkRepository;
use App\Repository\RuleLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/aulas')]
#[IsGranted('ROLE_ADMIN')]
class NetworkController extends AbstractController
{
    public function __construct(
        private readonly NetworkRepository $networkRepository,
        private readonly RuleLogRepository $ruleLogRepository,
    ) {
    }

    #[Route('', name: 'admin_network_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/network/index.html.twig', [
            'networks' => $this->networkRepository->findAllOrdered(),
        ]);
    }

    #[Route('/nueva', name: 'admin_network_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new Network());
    }

    #[Route('/{id}/editar', name: 'admin_network_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Network $network): Response
    {
        return $this->handleForm($request, $network);
    }

    #[IsCsrfTokenValid('delete_network', tokenKey: '_token')]
    #[Route('/{id}/eliminar', name: 'admin_network_delete', methods: ['POST'])]
    public function delete(Network $network): Response
    {
        if ($this->ruleLogRepository->countForNetworks([$network]) > 0) {
            $this->addFlash('error', sprintf(
                'No se puede eliminar "%s": tiene historial de accesos registrado.',
                $network->getName()
            ));

            return $this->redirectToRoute('admin_network_index');
        }

        $this->networkRepository->remove($network, true);
        $this->addFlash('success', sprintf('Aula "%s" eliminada.', $network->getName()));

        return $this->redirectToRoute('admin_network_index');
    }

    private function handleForm(Request $request, Network $network): Response
    {
        $isNew = $network->getId() === null;
        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('network_form', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $name = trim((string) $request->request->get('name', ''));
            $allowedIp = trim((string) $request->request->get('allowed_ip', ''));
            $ruleDescription = trim((string) $request->request->get('rule_description', ''));
            $level = (int) $request->request->get('level', 0);

            if ($name === '') {
                $errors[] = 'El nombre no puede estar vacío.';
            }
            if ($allowedIp === '') {
                $errors[] = 'La IP autorizada no puede estar vacía.';
            }
            if ($ruleDescription === '') {
                $errors[] = 'La descripción de regla no puede estar vacía.';
            }

            if ($errors === []) {
                $network->setName($name);
                $network->setAllowedIp($allowedIp);
                $network->setRuleDescription($ruleDescription);
                $network->setLevel($level);

                $this->networkRepository->save($network, true);
                $this->addFlash('success', sprintf('Aula "%s" guardada.', $network->getName()));

                return $this->redirectToRoute('admin_network_index');
            }
        }

        return $this->render('admin/network/form.html.twig', [
            'network' => $network,
            'is_new' => $isNew,
            'errors' => $errors,
        ]);
    }
}
