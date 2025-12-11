<x-layout>
    <x-logout />
    <x-creator :back="route('member.dashboard.index')" :action="route('member.urls.store')" />
</x-layout>
