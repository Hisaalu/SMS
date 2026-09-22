<?php
// File: /app/Controllers/StreamController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Stream;
use NexaT\Models\SchoolClass;

class StreamController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();
        $streams  = Stream::where('school_id', $schoolId);
        $classes  = SchoolClass::where('school_id', $schoolId);

        $classList = [];
        foreach ($classes as $c) {
            $classList[$c->id] = $c->name;
        }

        echo $this->view->renderWithLayout('academic/streams/index', 'default', [
            'streams'   => $streams,
            'classList' => $classList,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->view->renderWithLayout('academic/streams/create', 'default', [
            'classes' => SchoolClass::where('school_id', $this->schoolId()),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $classId = (int)($_POST['class_id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');

        if (!$classId || $name === '') {
            $this->flashError('Class and Stream Name are required.');
            $this->redirect('/academic/streams/create');
        }

        $stream = new Stream([
            'school_id' => $this->schoolId(),
            'class_id'  => $classId,
            'name'      => $name,
        ]);

        if ($stream->save()) {
            $this->flashSuccess('Stream created successfully.');
            $this->redirect('/academic/streams');
        }

        $this->flashError('Failed to create stream.');
        $this->redirect('/academic/streams/create');
    }

    public function edit($params): void
    {
        $this->requireAuth();

        $stream = $this->findStreamOrRedirect((int)($params['id'] ?? 0));
        if (!$stream) {
            return;
        }

        echo $this->view->renderWithLayout('academic/streams/edit', 'default', [
            'stream'  => $stream,
            'classes' => SchoolClass::where('school_id', $this->schoolId()),
        ]);
    }

    public function update($params): void
    {
        $this->requireAuth();

        $streamId = (int)($params['id'] ?? 0);
        $stream = $this->findStreamOrRedirect($streamId);
        if (!$stream) {
            return;
        }

        $classId = (int)($_POST['class_id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');

        if (!$classId || $name === '') {
            $this->flashError('Class and Stream Name are required.');
            $this->redirect('/academic/streams/' . $streamId . '/edit');
        }

        $stream->fill([
            'class_id' => $classId,
            'name'     => $name,
        ]);

        if ($stream->save()) {
            $this->flashSuccess('Stream updated successfully.');
            $this->redirect('/academic/streams');
        }

        $this->flashError('Failed to update stream.');
        $this->redirect('/academic/streams/' . $streamId . '/edit');
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
}