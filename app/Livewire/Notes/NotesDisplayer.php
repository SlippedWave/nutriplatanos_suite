<?php

namespace App\Livewire\Notes;

use Livewire\Component;

class NotesDisplayer extends Component
{
    public $query;
    public $user_id;
    public $notable_type;
    public $notable_id;

    public bool $showCreateNoteModal = false;
    public $content = '';

    public function mount($notable_type, $notable_id)
    {
        $this->notable_type = $notable_type;
        $this->notable_id = $notable_id;
        $this->user_id = auth()->user()->id;
        $this->loadNotes();
    }

    public function createNote()
    {
        $this->validate([
            'content' => 'required|string|max:1000',
        ]);

        \App\Models\Note::create([
            'notable_type' => $this->notable_type,
            'notable_id' => $this->notable_id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'type' => 'general',
        ]);

        $this->loadNotes();
        $this->content = '';
        $this->showCreateNoteModal = false;

        session()->flash('message', 'Nota añadida exitosamente!');
    }

    public function loadNotes()
    {
        $this->query = \App\Models\Note::where('notable_type', $this->notable_type)
            ->where('notable_id', $this->notable_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.notes.notes-displayer');
    }

    public function openCreateNoteModal()
    {
        $this->showCreateNoteModal = true;
        $this->content = ''; // Reset notes input
    }
}
