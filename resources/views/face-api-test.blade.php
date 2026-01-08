@extends('layouts.app')

@section('title', 'Face API Test')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold mb-4">Testing Face API Model Loading</h3>
    <p id="status-message" class="text-gray-600">Loading models...</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusMessage = document.getElementById('status-message');
    faceapi.loadModelsFromUri('/models').then(() => {
        statusMessage.textContent = 'Models loaded successfully without error.';
        statusMessage.className = 'text-green-600 font-semibold';
        console.log('Models loaded successfully without error.');
    }).catch(err => {
        statusMessage.textContent = 'Error loading models: ' + err.message;
        statusMessage.className = 'text-red-600 font-semibold';
        console.error('Error loading models:', err);
    });
});
</script>
@endsection