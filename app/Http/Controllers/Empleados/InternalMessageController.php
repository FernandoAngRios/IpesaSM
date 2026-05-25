<?php

namespace App\Http\Controllers\Empleados;

use App\Http\Controllers\Controller;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternalMessageController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $messages = $user->isAdmin()
            ? InternalMessage::with('sender')->forAdminInbox()->latest()->paginate(20)
            : InternalMessage::with('sender')->forEmployeeInbox($user->id)->latest()->paginate(20);

        return view('empleados.internal_messages.index', compact('messages'));
    }

    public function create(): View
    {
        $employees = auth()->user()->isAdmin()
            ? User::where('role', 'employee')->where('active', true)->orderBy('name')->get()
            : collect();

        return view('empleados.internal_messages.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'      => 'required|string|max:150',
            'body'         => 'required|string|max:2000',
            'recipient_id' => 'nullable|exists:users,id',
        ]);

        $user = auth()->user();

        // Empleado siempre escribe al buzón admin (recipient_id null lo identifica como eso)
        if (! $user->isAdmin()) {
            $data['recipient_id'] = null;
        }

        InternalMessage::create([
            'sender_id'    => $user->id,
            'recipient_id' => $data['recipient_id'] ?? null,
            'subject'      => $data['subject'],
            'body'         => $data['body'],
        ]);

        return redirect()->route('empleados.internal-messages.index')
            ->with('success', 'Mensaje enviado correctamente.');
    }

    public function show(InternalMessage $internalMessage): View
    {
        $user = auth()->user();

        abort_unless($this->canView($user, $internalMessage), 403);

        if (! $internalMessage->isRead() && $this->isRecipient($user, $internalMessage)) {
            $internalMessage->update(['read_at' => now()]);
        }

        return view('empleados.internal_messages.show', compact('internalMessage'));
    }

    public function destroy(InternalMessage $internalMessage): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $internalMessage->delete();

        return redirect()->route('empleados.internal-messages.index')
            ->with('success', 'Mensaje eliminado.');
    }

    private function canView(User $user, InternalMessage $message): bool
    {
        if ($user->isAdmin()) return true;

        // El remitente siempre puede ver su propio mensaje
        if ($message->sender_id === $user->id) return true;

        // Mensaje directo al empleado
        if ($message->recipient_id === $user->id) return true;

        // Broadcast del admin
        return $message->isBroadcast() && $message->sender->isAdmin();
    }

    private function isRecipient(User $user, InternalMessage $message): bool
    {
        return $message->recipient_id === $user->id
            || ($message->isBroadcast() && $message->sender->isAdmin());
    }
}
