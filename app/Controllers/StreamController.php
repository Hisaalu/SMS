<?php
// File: /app/Controllers/StreamController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Stream;
use NexaT\Models\SchoolClass;
use Throwable;

class StreamController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('classes.view');

        $schoolId = $this->schoolId();

        $streams = $this->db->fetchAll(
            "SELECT s.*, c.name AS class_name,
                    (SELECT COUNT(*) FROM student_enrollments se
                        WHERE se.stream_id = s.id AND se.status = 'active') AS students_count
             FROM streams s
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE s.school_id = :school_id
             ORDER BY c.name ASC, s.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('academic/streams/index', 'default', [
            'streams' => $streams,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('classes.create');

        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $this->schoolId()]
        );

        echo $this->view->renderWithLayout('academic/streams/create', 'default', [
            'classes' => $classes,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('classes.create');

        $classId = (int)($_POST['class_id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');

        if (!$classId || $name === '') {
            $this->flashError('Class and Stream Name are required.');
            $this->redirect('/academic/streams/create');
        }

        if (!$this->classBelongsToSchool($classId, $this->schoolId())) {
            $this->flashError('The selected class does not exist.');
            $this->redirect('/academic/streams/create');
        }

        if ($this->nameExists($this->schoolId(), $classId, $name)) {
            $this->flashError("A stream named \"{$name}\" already exists for this class.");
            $this->redirect('/academic/streams/create');
        }

        try {
            $stream = new Stream([
                'school_id' => $this->schoolId(),
                'class_id'  => $classId,
                'name'      => $name,
            ]);

            if (!$stream->save()) {
                throw new \RuntimeException('Save returned false.');
            }

            $this->audit('Stream Created', 'academics', "Created stream: {$name} (Class #{$classId})");
            $this->flashSuccess('Stream created successfully.');
            $this->redirect('/academic/streams');

        } catch (Throwable $e) {
            $this->flashError('Failed to create stream: ' . $e->getMessage());
            $this->redirect('/academic/streams/create');
        }
    }

    public function edit($params): void
    {
        $this->requirePermission('classes.edit');

        $stream = $this->findStreamOrRedirect((int)($params['id'] ?? 0));
        if (!$stream) {
            return;
        }

        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $this->schoolId()]
        );

        echo $this->view->renderWithLayout('academic/streams/edit', 'default', [
            'stream'  => $stream,
            'classes' => $classes,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('classes.edit');

        $streamId = (int)($params['id'] ?? 0);
        $stream   = $this->findStreamOrRedirect($streamId);
        if (!$stream) {
            return;
        }

        $classId = (int)($_POST['class_id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');

        if (!$classId || $name === '') {
            $this->flashError('Class and Stream Name are required.');
            $this->redirect('/academic/streams/' . $streamId . '/edit');
        }

        if (!$this->classBelongsToSchool($classId, $this->schoolId())) {
            $this->flashError('The selected class does not exist.');
            $this->redirect('/academic/streams/' . $streamId . '/edit');
        }

        if ($this->nameExists($this->schoolId(), $classId, $name, $streamId)) {
            $this->flashError("Another stream named \"{$name}\" already exists for this class.");
            $this->redirect('/academic/streams/' . $streamId . '/edit');
        }

        try {
            $stream->fill(['class_id' => $classId, 'name' => $name]);

            if (!$stream->save()) {
                throw new \RuntimeException('Save returned false.');
            }

            $this->audit('Stream Updated', 'academics', "Updated stream: {$name} (ID: {$streamId})");
            $this->flashSuccess('Stream updated successfully.');
            $this->redirect('/academic/streams');

        } catch (Throwable $e) {
            $this->flashError('Failed to update stream: ' . $e->getMessage());
            $this->redirect('/academic/streams/' . $streamId . '/edit');
        }
    }

    public function delete($params): void
    {
        $this->requirePermission('classes.delete');

        $streamId = (int)($params['id'] ?? 0);
        $stream   = Stream::find($streamId);

        if (!$stream) {
            $this->json(['error' => 'Stream not found.'], 404);
        }

        $enrollments = (int)($this->db->fetch(
            "SELECT COUNT(*) AS c FROM student_enrollments
             WHERE stream_id = :id AND status = 'active'",
            ['id' => $streamId]
        )['c'] ?? 0);

        if ($enrollments > 0) {
            $this->json([
                'error' => "Cannot delete this stream: {$enrollments} active enrollment(s) reference it."
            ], 409);
        }

        try {
            $name = $stream->name;

            if ($stream->delete(['id' => $streamId])) {
                $this->audit('Stream Deleted', 'academics', "Deleted stream: {$name}");
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Failed to delete stream.'], 500);

        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to delete stream: ' . $e->getMessage()], 500);
        }
    }

    private function findStreamOrRedirect(int $id): ?Stream
    {
        $stream = Stream::find($id);

        if (!$stream) {
            $this->flashError('Stream not found.');
            $this->redirect('/academic/streams');
            return null;
        }

        return $stream;
    }

    private function nameExists(int $schoolId, int $classId, string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM streams
                WHERE school_id = :school_id AND class_id = :class_id AND name = :name";
        $params = ['school_id' => $schoolId, 'class_id' => $classId, 'name' => $name];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        return (bool)$this->db->fetch($sql . " LIMIT 1", $params);
    }

    private function classBelongsToSchool(int $classId, int $schoolId): bool
    {
        return (bool)$this->db->fetch(
            "SELECT id FROM classes WHERE id = :id AND school_id = :school_id LIMIT 1",
            ['id' => $classId, 'school_id' => $schoolId]
        );
    }
}