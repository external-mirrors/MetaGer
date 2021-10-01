@extends('layouts.subPages')

@section('title', $title )

@section('content')
<style>
    #head {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }
    #head > button {
        margin-left: 16px;
    }
    #head > h1 {
        margin: 0;
    }
    #queries {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .matches {
        background-color: #c9f4c9;
    }
    #block-requests {
        margin-bottom: 16px;
    }
    #regexp {
        margin-bottom: 8px;
    }
    #ban-time {
        margin-bottom: 8px;
    }
</style>
<div id="block-requests">
    <form method="post">
        <input class="form-control" type="text" name="regexp" id="regexp" placeholder="Type in regexp to match queries...">
        <select name="ban-time" id="ban-time" class="form-control">
            <option value="1 day">Einen Tag</option>
            <option value="1 week">Eine Woche</option>
            <option value="2 weeks">Zwei Wochen</option>
            <option value="1 month" selected>Einen Monat</option>
        </select>
        <button type="submit" class="btn btn-default btn-sm">Sperren</button>
    </form>
</div>
<div id="head">
    <h1>Letzte Suchanfragen</h1>
    <button type="button" class="btn btn-success btn-sm">Aktualisierung stoppen (60)</button>
</div>
<input class="form-control" type="text" name="" id="check-against" placeholder="Match against...">
<div id="queries">
    @foreach($queries as $query)
    <div class="query card">{{$query}}</div>
    @endforeach
</div>
<div id="bans">
    <h1>Current Bans</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <td>Regexp</td>
                <td>Banned until</td>
                <td>Actions</td>
            </tr>
        </thead>
        <tbody>
            @foreach($bans as $ban)
            <tr>
                <td>{{ $ban["regexp"] }}</td>
                <td>{{ Carbon::createFromFormat("Y-m-d H:i:s", $ban["banned-until"])->format("d.m.Y H:i:s")}} ({{ Carbon::createFromFormat("Y-m-d H:i:s", $ban["banned-until"])->diffInDays(Carbon::now()) }} Days)</td>
                <td>
                    <form action="{{ url("admin/spam/deleteRegexp") }}" method="post">
                        <input type="hidden" name="regexp" value="{{ $ban["regexp"] }}">
                        <button type="submit">&#128465;</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div id="loadedbans">
    <h1>Loaded Bans</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <td>Regexp</td>
            </tr>
        </thead>
        <tbody>
            @foreach($loadedBans as $ban)
            <tr>
                <td>{{ $ban }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    var lastUpdate = Date.now();
    var updating = true;
    var buttonText = "Aktualisierung stoppen";
    var interval = setInterval(updateQueries, 1000);
    document.getElementById("regexp").oninput = checkRegexp;
    document.getElementById("check-against").oninput = checkRegexp;
    window.addEventListener('load', function(){
        checkRegexp();
    });


    document.querySelector("#head > button").addEventListener('click', function() {
        if(!updating) {
            $("#head > button").removeClass("btn-danger");
            $("#head > button").addClass("btn-success");
            buttonText = "Aktualisierung stoppen";
            interval = setInterval(updateQueries, 1000);
        }
        var updateAt = lastUpdate + 60000;
        var updateIn = Math.round((updateAt - Date.now()) / 1000);
        $("#head > button").html(buttonText + " (" + updateIn + ")");
        updating = !updating;
    })

    function updateQueries() {
        var updateAt = lastUpdate + 60000;
        var updateIn = Math.round((updateAt - Date.now()) / 1000);

        if(!updating){
            document.querySelector("#head > button").classList.remove("btn-success");
            document.querySelector("#head > button").classList.add("btn-danger");
            buttonText = "Aktualisierung starten";
            clearInterval(interval);
        }

        document.querySelector("#head > button").innerHTML = buttonText + " (" + updateIn + ")";
        if(updateAt > Date.now()){
            return;
        }
        fetch("{{ url('admin/spam/jsonQueries') }}")
            .then(response => response.json())
            .then(data => {
                document.getElementById("queries").innerHTML = "";
                data.forEach((index, el) => {
                    newElement = document.createElement("div");
                    newElement.classList.add("query");
                    newElement.classList.add("card");
                    newElement.innerHTML = el;
                    document.getElementById("queries").appendChild(newElement);
                });
                lastUpdate = Date.now();
                checkRegexp();
            });

    }


    function checkRegexp() {
        var val = document.getElementById("regexp").value;
        var queries = [];


        document.querySelectorAll("#queries > .query").forEach((el, index) => {
            queries.push(el.innerHTML);
        });
        

        queries.push(document.getElementById("check-against").value)

        var url = "{{ url('admin/spam/queryregexp') }}";
        var options = {
            method: 'POST',
            body: JSON.stringify({
                "queries": queries,
                "regexp": val
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        };

        fetch(url, options)
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll("#queries > .query").forEach((el, index) => {
                    if(data[index]["matches"]){
                        el.classList.add("matches");
                    }else{
                        el.classList.remove("matches");
                    }
                });
                
                if(data[data.length-1]["matches"]){
                    document.getElementById("check-against").classList.add("matches");
                }else{
                    document.getElementById("check-against").classList.remove("matches");
                }
            });
    }


</script>
@endsection
