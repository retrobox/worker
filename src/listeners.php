<?php
$client->addListener('note.stored', [\App\Controllers\NoteController::class, 'stored']);
$client->addListener('note.updated', [\App\Controllers\NoteController::class, 'updated']);
$client->addListener('note.destroyed', [\App\Controllers\NoteController::class, 'destroyed']);