<?php
// File: websocket/server.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class NoteCollaboration implements MessageComponentInterface {

    // Lưu tất cả connections
    protected \SplObjectStorage $clients;

    // Lưu mapping: note_id → [connections]
    protected array $noteRooms = [];

    // Lưu mapping: resourceId → user info
    protected array $userInfo = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        echo "[Server] NoteApp WebSocket Server started!\n";
    }

    // ===== KHI CÓ CLIENT KẾT NỐI =====
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "[Server] New connection: {$conn->resourceId}\n";

        // Gửi xác nhận kết nối
        $conn->send(json_encode([
            'type'        => 'connected',
            'resource_id' => $conn->resourceId,
            'message'     => 'Kết nối thành công!'
        ]));
    }

    // ===== KHI NHẬN TIN NHẮN =====
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!$data || !isset($data['type'])) return;

        switch ($data['type']) {

            // Client tham gia phòng chỉnh sửa note
            case 'join':
                $this->handleJoin($from, $data);
                break;

            // Client gửi thay đổi nội dung note
            case 'edit':
                $this->handleEdit($from, $data);
                break;

            // Client rời phòng
            case 'leave':
                $this->handleLeave($from, $data);
                break;

            // Client ping để giữ kết nối
            case 'ping':
                $from->send(json_encode(['type' => 'pong']));
                break;
        }
    }

    // ===== XỬ LÝ JOIN ROOM =====
    private function handleJoin(ConnectionInterface $conn, array $data): void {
        $noteId      = $data['note_id']   ?? null;
        $userId      = $data['user_id']   ?? null;
        $displayName = $data['user_name'] ?? 'Ẩn danh';

        if (!$noteId || !$userId) return;

        // Lưu thông tin user
        $this->userInfo[$conn->resourceId] = [
            'user_id'   => $userId,
            'user_name' => $displayName,
            'note_id'   => $noteId
        ];

        // Thêm vào phòng note
        if (!isset($this->noteRooms[$noteId])) {
            $this->noteRooms[$noteId] = [];
        }
        $this->noteRooms[$noteId][$conn->resourceId] = $conn;

        echo "[Server] User $displayName joined note #$noteId\n";

        // Thông báo cho các user khác trong phòng
        $this->broadcastToRoom($noteId, [
            'type'      => 'user_joined',
            'user_id'   => $userId,
            'user_name' => $displayName,
            'message'   => "$displayName đã tham gia chỉnh sửa",
            'users'     => $this->getRoomUsers($noteId)
        ], $conn->resourceId);

        // Gửi danh sách user hiện tại cho người vừa join
        $conn->send(json_encode([
            'type'  => 'room_info',
            'users' => $this->getRoomUsers($noteId)
        ]));
    }

    // ===== XỬ LÝ EDIT =====
    private function handleEdit(ConnectionInterface $from, array $data): void {
        $noteId  = $data['note_id'] ?? null;
        $field   = $data['field']   ?? ''; // 'title' hoặc 'content'
        $value   = $data['value']   ?? '';
        $userId  = $data['user_id'] ?? null;

        if (!$noteId) return;

        // Lưu vào DB
        $this->saveToDatabase($noteId, $field, $value, $userId);

        // Broadcast cho tất cả user khác trong phòng
        $this->broadcastToRoom($noteId, [
            'type'    => 'edit',
            'note_id' => $noteId,
            'field'   => $field,
            'value'   => $value,
            'user_id' => $userId
        ], $from->resourceId); // Trừ người gửi

        echo "[Server] Note #$noteId edited by user #$userId\n";
    }

    // ===== XỬ LÝ LEAVE =====
    private function handleLeave(ConnectionInterface $conn, array $data): void {
        $noteId = $data['note_id'] ?? null;
        if (!$noteId) return;

        $this->removeFromRoom($conn, $noteId);
    }

    // ===== KHI CLIENT NGẮT KẾT NỐI =====
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        // Xóa khỏi phòng nếu đang trong phòng
        $info = $this->userInfo[$conn->resourceId] ?? null;
        if ($info) {
            $noteId = $info['note_id'];
            $this->removeFromRoom($conn, $noteId);
            unset($this->userInfo[$conn->resourceId]);
        }

        echo "[Server] Connection closed: {$conn->resourceId}\n";
    }

    // ===== KHI CÓ LỖI =====
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "[Server] Error: {$e->getMessage()}\n";
        $conn->close();
    }

    // ===== HELPERS =====

    // Gửi tin đến tất cả trong phòng (trừ sender)
    private function broadcastToRoom(
        $noteId,
        array $data,
        ?int $excludeResourceId = null
    ): void {
        if (!isset($this->noteRooms[$noteId])) return;

        $message = json_encode($data);

        foreach ($this->noteRooms[$noteId] as $resourceId => $conn) {
            if ($resourceId !== $excludeResourceId) {
                $conn->send($message);
            }
        }
    }

    // Xóa connection khỏi phòng
    private function removeFromRoom(
        ConnectionInterface $conn,
        $noteId
    ): void {
        if (!isset($this->noteRooms[$noteId])) return;

        $info = $this->userInfo[$conn->resourceId] ?? null;
        unset($this->noteRooms[$noteId][$conn->resourceId]);

        // Thông báo cho user khác
        if ($info) {
            $this->broadcastToRoom($noteId, [
                'type'      => 'user_left',
                'user_id'   => $info['user_id'],
                'user_name' => $info['user_name'],
                'message'   => "{$info['user_name']} đã rời khỏi",
                'users'     => $this->getRoomUsers($noteId)
            ]);
        }

        // Xóa phòng nếu trống
        if (empty($this->noteRooms[$noteId])) {
            unset($this->noteRooms[$noteId]);
        }

        echo "[Server] User left note #$noteId\n";
    }

    // Lấy danh sách users trong phòng
    private function getRoomUsers($noteId): array {
        if (!isset($this->noteRooms[$noteId])) return [];

        $users = [];
        foreach ($this->noteRooms[$noteId] as $resourceId => $conn) {
            if (isset($this->userInfo[$resourceId])) {
                $users[] = [
                    'user_id'   => $this->userInfo[$resourceId]['user_id'],
                    'user_name' => $this->userInfo[$resourceId]['user_name']
                ];
            }
        }
        return $users;
    }

    // Lưu thay đổi vào DB
    private function saveToDatabase(
        $noteId,
        string $field,
        string $value,
        $userId
    ): void {
        try {
            $db = getDB();

            if ($field === 'title') {
                $stmt = $db->prepare("
                    UPDATE notes
                    SET title = ?, updated_at = NOW()
                    WHERE id = ?
                ");
            } elseif ($field === 'content') {
                $stmt = $db->prepare("
                    UPDATE notes
                    SET content = ?, updated_at = NOW()
                    WHERE id = ?
                ");
            } else {
                return;
            }

            $stmt->execute([$value, $noteId]);

        } catch (\Exception $e) {
            echo "[Server] DB Error: {$e->getMessage()}\n";
        }
    }
}

// ===== KHỞI ĐỘNG SERVER =====
$port = 8080;

echo "========================================\n";
echo " NoteApp WebSocket Server\n";
echo " Port: $port\n";
echo " ws://localhost:$port\n";
echo "========================================\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NoteCollaboration()
        )
    ),
    $port
);

$server->run();