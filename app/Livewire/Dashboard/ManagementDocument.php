<?php

namespace App\Livewire\Dashboard;

use App\Models\Document;
use App\Services\DocumentExportService;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManagementDocument extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $name = '';
    public $type = 'other';
    public $title = '';
    public $description = '';
    public $content = '';
    public $is_public = true;
    public $export_format = 'pdf';
    public $data_source = '';
    public $selected_columns = [];
    public $layout_settings = [];
    public $activeTab = 'general';

    public $logo_left = null;
    public $logo_right = null;
    public $existingLogoLeft = null;
    public $existingLogoRight = null;

    public $editingDocumentId = null;
    public $documentToDelete = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalMode = 'create';

    public function mount()
    {
        $this->layout_settings = Document::defaultLayout();
    }

    public function render()
    {
        $query = Document::query()->whereNull('event_uid');
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('title', 'like', "%{$this->search}%")
                  ->orWhere('type', 'like', "%{$this->search}%");
            });
        }

        return view('livewire.dashboard.management-document', [
            'documents' => $query->latest()->paginate(10),
            'dataSources' => Document::dataSources(),
        ]);
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'type', 'title', 'description', 'content', 'is_public', 'export_format', 'data_source', 'selected_columns', 'logo_left', 'logo_right', 'existingLogoLeft', 'existingLogoRight', 'editingDocumentId']);
        $this->layout_settings = Document::defaultLayout();
        $this->activeTab = 'general';
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal($uid)
    {
        $doc = Document::where('uid', $uid)->firstOrFail();
        $this->editingDocumentId = $doc->uid;
        $this->name = $doc->name;
        $this->type = $doc->type;
        $this->title = $doc->title;
        $this->description = $doc->description;
        $this->content = $doc->content;
        $this->is_public = $doc->is_public;
        $this->export_format = $doc->export_format ?? 'pdf';
        $this->data_source = $doc->data_source ?? '';
        $this->selected_columns = $doc->selected_columns ?? [];
        $this->layout_settings = $doc->layout_settings ?? Document::defaultLayout();
        $this->existingLogoLeft = $doc->logo_left;
        $this->existingLogoRight = $doc->logo_right;
        $this->activeTab = 'general';
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize($this->modalMode === 'create' ? 'setting-document.create' : 'setting-document.edit');

        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'is_public' => 'boolean',
            'logo_left' => 'nullable|image|max:5120',
            'logo_right' => 'nullable|image|max:5120',
        ]);

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'is_public' => $this->is_public,
            'export_format' => $this->export_format,
            'data_source' => $this->data_source ?: null,
            'selected_columns' => $this->selected_columns,
            'layout_settings' => $this->layout_settings,
            'event_uid' => null,
        ];

        if ($this->logo_left) {
            $data['logo_left'] = $this->logo_left->store('documents/logos', 'public');
        }
        if ($this->logo_right) {
            $data['logo_right'] = $this->logo_right->store('documents/logos', 'public');
        }

        if ($this->modalMode === 'create') {
            $data['uid'] = (string) Str::uuid();
            Document::create($data);
            $msg = 'Template dokumen berhasil dibuat!';
        } else {
            Document::where('uid', $this->editingDocumentId)->firstOrFail()->update($data);
            $msg = 'Template dokumen berhasil diperbarui!';
        }

        $this->showModal = false;
        $this->dispatch('notification', ['status' => 'success', 'message' => $msg]);
    }

    public function confirmDelete($uid)
    {
        $this->documentToDelete = Document::where('uid', $uid)->firstOrFail();
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->authorize('setting-document.delete');
        if ($this->documentToDelete) {
            $this->documentToDelete->delete();
            $this->showDeleteModal = false;
            $this->documentToDelete = null;
            $this->dispatch('notification', ['status' => 'success', 'message' => 'Template berhasil dihapus']);
        }
    }

    public function exportPreviewPdf($uid = null)
    {
        if ($uid) {
            $doc = Document::where('uid', $uid)->firstOrFail();
        } else {
            // Build temporary document from current form state
            $doc = new Document([
                'name' => $this->name,
                'type' => $this->type,
                'title' => $this->title,
                'description' => $this->description,
                'content' => $this->content,
                'data_source' => $this->data_source,
                'selected_columns' => $this->selected_columns,
                'layout_settings' => $this->layout_settings,
                'logo_left' => $this->existingLogoLeft,
                'logo_right' => $this->existingLogoRight,
            ]);
        }

        $pdf = DocumentExportService::generatePdf($doc);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, Str::slug($doc->name ?: 'preview') . '.pdf');
    }

    public function getDummyRows()
    {
        if (empty($this->selected_columns)) return [];
        return DocumentExportService::getDummyData($this->data_source, $this->selected_columns);
    }
}
