@extends('layouts.admin')

@section('content')
    <div class="container">
        <h4 class="mb-3 text-3xl"
            style="color: #2f5365 font-size: 26px;
    font-weight: 700;
    color: #2a1a05;
    letter-spacing: 1.5px;
    font-family: Georgia, serif;
    margin: 0;
}">
            Edit Footer Settings</h4>

        <form action="{{ route('admin.footer.update') }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            {{-- Tagline --}}
            <div class="mb-3">
                <label>Tagline</label>
                <textarea name="tagline" class="form-control" rows="2">{{ old('tagline', $settings->tagline) }}</textarea>
                @error('tagline')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Description with CKEditor --}}
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" id="description" rows="5" class="form-control">{{ old('description', $settings->description) }}</textarea>
                @error('description')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Copyright --}}
            <div class="mb-3">
                <label>Copyright Text</label>
                <input type="text" name="copyright_text" class="form-control"
                    value="{{ old('copyright_text', $settings->copyright_text) }}">
                @error('copyright_text')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Social Media --}}
            <div class="mb-3">
                <label>YouTube URL</label>
                <input type="url" name="youtube_url" class="form-control"
                    value="{{ old('youtube_url', $settings->youtube_url) }}">
                @error('youtube_url')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Instagram URL</label>
                <input type="url" name="instagram_url" class="form-control"
                    value="{{ old('instagram_url', $settings->instagram_url) }}">
                @error('instagram_url')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Twitter URL</label>
                <input type="url" name="twitter_url" class="form-control"
                    value="{{ old('twitter_url', $settings->twitter_url) }}">
                @error('twitter_url')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Contact --}}
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $settings->email) }}">
                @error('email')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="contact_phone" class="form-control"
                    value="{{ old('contact_phone', $settings->contact_phone) }}">
                @error('contact_phone')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label>GST Invoice Number</label>
                <input type="text" name="gst_in" class="form-control" value="{{ old('gst_in', $settings->gst_in) }}">
                @error('gst_in')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label>Address</label>
                <textarea name="contact_address" class="form-control" rows="3">{{ old('contact_address', $settings->contact_address) }}</textarea>
                @error('contact_address')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            {{-- Buttons --}}
            <button
                style="color: #2a1a05;
                font-family: Georgia, serif;
                margin: 0;
                color:white;
                background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);"
                type="submit" class="btn btn-primary">Update</button>
            <a style="color: #2a1a05;
                font-family: Georgia, serif;
                margin: 0;
                color:white;
                background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);"
                href="{{ route('admin.footer.index') }}" class="btn ">Back</a>
        </form>
    </div>

    {{-- CKEditor 5 CDN --}}
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let editorInstance;
            const textarea = document.getElementById('description');

            if (textarea) {
                ClassicEditor.create(textarea, {
                    toolbar: ['bold', 'italic', 'bulletedList', 'numberedList', 'link', 'undo', 'redo']
                }).then(editor => {
                    editorInstance = editor;
                }).catch(error => console.error(error));
            }

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    if (editorInstance) {
                        editorInstance.updateSourceElement();
                    }
                });
            }
        });
    </script>
@endsection
