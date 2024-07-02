<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class UserList extends Component
{
    public $search = '';
    public $name;
    public $rol;
    public $userId;
    public $modalEditar = false;
    public $modalEliminar = false;


    public function openModalEditar(User $user) {
        $this->fill([
            'userId' => $user->id,
            'name' => $user->name,
            'rol' => $user->role->id,
        ]);
        $this->modalEditar = true;
    }


    public function saveItem()
    {
        $this->validate([
            'name' => 'required|string',
            'rol' => 'required' // Asegúrate de que el rol existe en la tabla roles
        ]);

        $data = [
            'name' => $this->name,
            'role_id' =>  $this->rol,
        ];



        // dd($data);

        try {
            if ($this->userId) {
                // Actualizar un usuario existente
                $user = User::findOrFail($this->userId);
                $user->update($data);
                session()->flash('success', '¡Usuario actualizado satisfactoriamente!');
            } else {
                // Crear un nuevo usuario
                User::create($data);
                session()->flash('success', '¡Usuario creado satisfactoriamente!');
            }
            // Cerrar el modal y resetear los campos
            $this->resetInputFields();
            $this->modalEditar = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Hubo un error al guardar el usuario: ' . $e->getMessage());
        }
    }

    private function resetInputFields()
    {
        $this->reset(['name', 'rol', 'userId']);
    }

    public function render()
    {
        $users = User::when($this->search, function ($query) {
            $query->where('name', 'like', "%{$this->search}%");
        })
        ->paginate(10);

        return view('livewire.user-list', [
            'users' => $users,
        ]);
    }
}
