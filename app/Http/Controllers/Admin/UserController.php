<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users')
            ->with('success', "User account has been {$status}.");
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->is_active) {
            return redirect()->route('admin.users')
                ->with('error', 'Cannot delete an active user. Please deactivate the user first.');
        }

        $user->delete();
        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }
}
