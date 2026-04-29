<?php
class BroadcastController extends Controller
{
    private Broadcast $broadcastModel;

    public function __construct()
    {
        parent::__construct();
        $this->broadcastModel = new Broadcast();
    }

    public function index(): void
    {
        $this->requireAuth();
        $broadcasts = $this->broadcastModel->getAll();
        $this->viewWithLayout('broadcasts/index', [
            'title' => 'Broadcasts',
            'broadcasts' => $broadcasts,
        ]);
    }

    public function manage(): void
    {
        $this->requireWorker();
        $broadcasts = $this->broadcastModel->getAllAdmin();
        $this->viewWithLayout('broadcasts/manage', [
            'title' => 'Manage Broadcasts',
            'broadcasts' => $broadcasts,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    public function create(): void
    {
        $this->requireWorker();
        $title = trim($this->post('title', ''));
        $message = trim($this->post('message', ''));
        $zone = trim($this->post('zone', '')) ?: null;
        $scheduledAt = trim($this->post('scheduled_at', '')) ?: null;

        if (empty($title) || empty($message)) {
            $this->setFlash('error', 'Title and message are required.');
            $this->redirect(BASE_URL . '/admin/broadcasts');
            return;
        }

        $this->broadcastModel->createBroadcast($title, $message, $zone, $_SESSION['user']['id'], $scheduledAt);
        $this->setFlash('success', 'Broadcast created successfully.');
        $this->redirect(BASE_URL . '/admin/broadcasts');
    }

    public function update(): void
    {
        $this->requireWorker();
        $id = (int)$this->post('id', 0);
        $title = trim($this->post('title', ''));
        $message = trim($this->post('message', ''));
        $zone = trim($this->post('zone', '')) ?: null;
        $scheduledAt = trim($this->post('scheduled_at', '')) ?: null;

        if ($id <= 0 || empty($title) || empty($message)) {
            $this->setFlash('error', 'Invalid input.');
            $this->redirect(BASE_URL . '/admin/broadcasts');
            return;
        }

        $this->broadcastModel->updateBroadcast($id, $title, $message, $zone, $scheduledAt);
        $this->setFlash('success', 'Broadcast updated successfully.');
        $this->redirect(BASE_URL . '/admin/broadcasts');
    }

    public function delete(): void
    {
        $this->requireWorker();
        $id = (int)$this->post('id', 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Invalid broadcast ID.');
            $this->redirect(BASE_URL . '/admin/broadcasts');
            return;
        }

        $this->broadcastModel->delete($id);
        $this->setFlash('success', 'Broadcast deleted.');
        $this->redirect(BASE_URL . '/admin/broadcasts');
    }
}