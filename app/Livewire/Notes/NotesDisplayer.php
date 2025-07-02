<?php

namespace App\Livewire\Notes;

use Livewire\Component;

class NotesDisplayer extends Component
{
    public $notes;
    public $user_id;
    public $notable_type;
    public $notable_id;

    public function mount($notable_type, $notable_id)
    {
        $this->notable_type = $notable_type;
        $this->notable_id = $notable_id;
        $this->user_id = auth()->user()->id;
        $this->loadNotes();
    }

    public function loadNotes()
    {
        $this->notes = \App\Models\Note::where('notable_type', $this->notable_type)
            ->where('notable_id', $this->notable_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.notes.notes-displayer');
    }
}
