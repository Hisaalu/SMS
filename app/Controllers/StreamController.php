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
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $streams = Stream::where('school_id', $schoolId);
        $classes = SchoolClass::where('school_id', $schoolId);

        // Map class names to streams for simple listing
        $classList = [];
        foreach ($classes as $c) {
            $classList[$c->id] = $c->name;
        }

        echo $this->view->renderWithLayout('academic/streams/index', 'default', [
            'streams'   => $streams,
            'classList' => $classList
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $classes  = SchoolClass::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/streams/create', 'default', [
            'classes' => $classes
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $classId  = $_POST['class_id'] ?? '';
        $name     = $_POST['name'] ?? '';

        if (empty($classId) || empty($name)) {
            $this->view->flash('error', 'Class and Stream Name are required.');
            header('Location: ' . BASE_URL . '/academic/streams/create');
            exit;
        }

        $stream = new Stream([
            'school_id' => $schoolId,
            'class_id'  => $classId,
            'name'      => $name
        ]);

        if ($stream->save()) {
            $this->view->flash('success', 'Stream created successfully.');
            header('Location: ' . BASE_URL . '/academic/streams');
            exit;
        }

        $this->view->flash('error', 'Failed to create stream.');
        header('Location: ' . BASE_URL . '/academic/streams/create');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $streamId = $params['id'] ?? 0;
        $stream   = Stream::find($streamId);

        if (!$stream) {
            $this->view->flash('error', 'Stream not found.');
            header('Location: ' . BASE_URL . '/academic/streams');
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $classes  = SchoolClass::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/streams/edit', 'default', [
            'stream'  => $stream,
            'classes' => $classes
        ]);
    }

    public function update($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $streamId = $params['id'] ?? 0;
        $stream   = Stream::find($streamId);

        if (!$stream) {
            $this->view->flash('error', 'Stream not found.');
            header('Location: ' . BASE_URL . '/academic/streams');
            exit;
        }

        $classId = $_POST['class_id'] ?? '';
        $name    = $_POST['name'] ?? '';

        if (empty($classId) || empty($name)) {
            $this->view->flash('error', 'Class and Stream Name are required.');
            header('Location: ' . BASE_URL . '/academic/streams/' . $streamId . '/edit');
            exit;
        }

        $stream->fill([
            'class_id' => $classId,
            'name'     => $name
        ]);

        if ($stream->save()) {
            $this->view->flash('success', 'Stream updated successfully.');
            header('Location: ' . BASE_URL . '/academic/streams');
            exit;
        }

        $this->view->flash('error', 'Failed to update stream.');
        header('Location: ' . BASE_URL . '/academic/streams/' . $streamId . '/edit');
        exit;
    }
}