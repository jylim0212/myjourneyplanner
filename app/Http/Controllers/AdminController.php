<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Journey;
use App\Models\JourneyLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function deleteUser(User $user)
    {
        try {
            // Prevent admin from deleting themselves
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users')
                    ->with('error', 'You cannot delete your own account.');
            }

            $user->delete();
            return redirect()->route('admin.users')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            return redirect()->route('admin.users')
                ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent admin from modifying themselves
            if ($user->id === auth()->id()) {
                return redirect()->route('admin.users')
                    ->with('error', 'You cannot modify your own account.');
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8|confirmed'
            ]);

            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return redirect()->route('admin.users')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage());
            return redirect()->route('admin.users')
                ->with('error', 'Failed to update user. Please try again.');
        }
    }

    public function weatherApi()
    {
        return view('admin.weather.index');
    }

    public function gptApi()
    {
        return view('admin.gpt.index');
    }
} 