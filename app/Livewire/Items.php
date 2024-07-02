<?php

namespace App\Livewire;

use App\Models\Item;
use App\Mail\ContactUs;
use Livewire\Component;
use Illuminate\Http\File;
use Livewire\WithPagination;
use App\Notifications\SendFormat;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class Items extends Component
{
    use WithPagination;

    public $search = '';
    public $active = false;
    public $itemId = null;
    public $name;
    public $status;
    public $price;
    public $sortField = 'name'; // Campo por el cual ordenar
    public $sortDirection = 'asc'; // Dirección del ordenamiento (asc o desc)

    // DropZone
    public $banners = []; // Inicializa como un array vacío
    public $files = []; // Inicializa como un array vacío

    //Send Email
    public $email;
    public $subject;
    public $message;


    public array $photos = [];

    public function submit(): void
    {
        foreach ($this->photos as $photo) {
            Storage::putFile('photos', new File($photo['path']));
        }
    }
    // Modal
    public $confirmingItemDeletion = false;
    public $confirmingItemAdd = false;
    public $sendingEmailModal = false;

    protected $queryString = [
        'active' => ['except' => false],
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $rules = [
        'name' => 'required|string|min:4',
        'price' => 'required|numeric|between:1,100',
        'status' => 'required|boolean',
    ];


    // Eliminar
    public function confirmItemDeletion($id)
    {
        $this->confirmingItemDeletion = $id;
    }
    public function deleteItem(Item $item)
    {
        $item->delete();
        $this->confirmingItemDeletion = false;
        session()->flash('success', 'Item Eliminado satisfactoriamente!');

    }

    // Crear
    public function confirmItemAdd()
    {
        $this->reset(['itemId', 'name', 'price', 'status']);
        $this->confirmingItemAdd = true;
    }

    // Editar
    public function confirmItemEdit(Item $item)
    {
        $this->fill([
            'itemId' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'status' => $item->status,
        ]);
        $this->confirmingItemAdd = true;
    }

    // Enviar Correo
    public function showModalEmail(Item $item)
    {
        $this->sendingEmailModal = true;
    }

    // Guardar Edit / Add
    public function saveItem()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'price' => $this->price,
            'status' => $this->status,
        ];

        if ($this->itemId) {
            $item = Item::find($this->itemId);
            $item->update($data);
            session()->flash('success', 'Item Actualizdo satisfactoriamente!');

        } else {
            $data['user_id'] = auth()->user()->id;
            Item::create($data);
            session()->flash('success', 'Item Agregado satisfactoriamente!');
        }

        $this->reset(['itemId', 'name', 'price', 'status']);
        $this->confirmingItemAdd = false;
    }

    // Cambiar el orden
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Send Email
    public function sendEmail()
    {
        $validatedData = $this->validate([
            'email' => 'required|email',
            'subject' => 'required|string|min:4',
            'message' => 'required|string|min:4|max:2500',
            'files.*' => 'nullable'
        ]);

        try {
            // Obtener los archivos validados
            $documentos = $this->files;
            // dd($documentos);

            // Enviar la notificación con los archivos adjuntos
            Notification::route('mail', $validatedData['email'])
                ->notify(new SendFormat($documentos, $validatedData['subject'], $validatedData['message']));

            session()->flash('success', 'Correo Enviado Correctamente!');
        } catch (\Throwable $th) {
            session()->flash('error', 'Hubo un error al enviar el correo.');
            // Puedes agregar un mensaje de error más específico usando $th->getMessage() si es necesario.
        }

        $this->reset(['email', 'subject', 'message', 'files']);
        $this->sendingEmailModal = false;
    }


    public function render()
    {
        $items = Item::where('user_id', auth()->user()->id)
            ->when($this->active, function ($query) {
                $query->where('status', 1);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $count = Item::count();

        return view('livewire.items', [
            'items' => $items,
            'count' => $count
        ]);
    }
}

