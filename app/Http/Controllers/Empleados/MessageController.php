<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class MessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest()->paginate(20);
        return view('empleados.messages.index', compact('messages'));
    }

    public function show(ContactMessage $message)
    {
        if (! $message->isRead()) {
            $message->update(['read_at' => now()]);
        }

        return view('empleados.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        return redirect()->route('empleados.messages.index')
            ->with('success', 'Mensaje eliminado.');
    }
}
