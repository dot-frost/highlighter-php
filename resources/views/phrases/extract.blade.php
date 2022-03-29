@extends('layout')
@section('content')
    <div class="min-h-screen w-full p-3">
        <table class="table w-full">
            <!-- head -->
            <thead>
            <tr>
                <th>Text</th>
                <th>Meaning</th>
                <th>Options</th>
                <th>Examples</th>
                <th class="print:hidden">Voices</th>
            </tr>
            </thead>
            <tbody>
            @foreach($phrases as $phrase)
                <tr>
                    <td>
                        <span>{{ $phrase->phrase }}</span>
                    </td>
                    <td>
                        @foreach($phrase->information['meaning'] as $language => $meaning)
                            {{ Str::upper($language) }}: <span>{{ $meaning }}</span><br/>
                        @endforeach
                    </td>
                    <td>
                        @foreach($phrase->information['options'] as $option => $value)
                            <span class="badge badge-info">{{ Str::studly($option) }}: {{ $value }}</span><br/>
                        @endforeach
                    </td>
                    <th>
                        @foreach($phrase->information['examples'] as $example => $meaning)
                            <span>{{ $example }}</span>
                            <br>
                            <span class="badge badge-ghost badge-md">Meaning: {{ $meaning }}</span>
                            <div class="divider m-0"></div>
                        @endforeach
                    </th>
                    <td class="print:hidden">
                        @foreach($phrase->information['voices'] as $voice => $value)
                            <span class="badge badge-info">{{ Str::upper($voice) }}: {{ $value }}</span><br/>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
            <!-- foot -->
            <tfoot>
            </tfoot>

</table>
    </div>
@endsection
