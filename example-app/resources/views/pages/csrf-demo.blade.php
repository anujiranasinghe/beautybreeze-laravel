<x-layouts.guest>
    <h1>CSRF Demo</h1>
    <form method="POST" action="{{ route('csrf.demo.submit') }}">
        @csrf
        <button type="submit">Submit</button>
    </form>
</x-layouts.guest>