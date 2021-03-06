@extends('layouts.app')
@php
  if ($story->finished){
    $class = 'warning';
  } else {
    $class = ($story->open)?'success':'primary';
  }
@endphp

@section('content')

<div class="card border border-{{$class}}">
    <!-- Card content -->
    <div class="card-body position-relative">
        <!-- Title -->
        <h2 class="card-title mt-1">{{$story->title}}</h4>
        <div style="margin-top:6px;right: 28px;" class="text-muted position-absolute">{{$story->created_at}}</div>
        <!-- Text -->
        <h4 class="card-text mb-3"><a href="{{route('users.show',$story->author->id)}}">{{$story->author->name}}</a></h4>
        <hr>
        <!-- Button -->
        <p class="pb-1">
            @forelse($story->keywords as $keyword)
                <a class="rounded-pill mr-2 btn-sm btn-primary" href="{{route('keywords.show',$keyword->id)}}">#{{$keyword->word}}</a>
            @empty
            No keywords
            @endforelse
        </p>
        @component('components.rating',['instance'=>$story,'entity'=>'App\Story'])
        @endcomponent

        <table class="table mt-2">
            <thead>
                <tr>
                    <th scope="col">User</th>
                    <th scope="col">sentence</th>
                    <th scope="col" style="width: 110px">rating</th>
                    <th scope="col">contributions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($story->sentences()->withTrashed()->get() as $sentence)
                <tr>
                    <td>
                        @if(!$sentence->trashed())
                            <a href="{{route('users.show',$sentence->author->id)}}">{{$sentence->author->name}}</a>
                        @else
                            deleted
                        @endif
                    </td>
                    <td>
                        @if($sentence->trashed())<s>@endif<a class="text-muted" href="{{route('sentences.show',$sentence->id)}}">
                            #{{$sentence->id}}</a> 
                            @if(!$sentence->trashed() && $sentence->author_id == \Auth::id())<a href="{{route('sentences.edit',$sentence->id)}}" class="close mr-1" aria-label="Close"><img src="https://img.icons8.com/material-two-tone/18/000000/pencil-tip.png"> @endif
        </a>{{$sentence->text}}
                        @if($sentence->trashed())</s>@endif
                    </td>
                    <td>
                    @if(!$sentence->trashed())
                        @component('components.rating',['instance'=>$sentence,'entity'=>'App\Sentence'])
                        @endcomponent
                    @endif
                    </td>
                        <td class="text-center">
                            @if(!$sentence->trashed())
                                {{App\Sentence::where('author_id',$sentence->author->id)->where('story_id',$story->id)->count()}}
                            @endif 
                        </td>                   
                </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{route('stories.index')}}" class="btn btn-primary">Back</a>
        @if(Auth::user()->id == $story->user_id || Auth::user()->isAdmin())
        <a href="{{route('stories.edit',$story->id)}}" class="btn btn-primary">Edit</a>
        <form class="d-inline" action="{{route('stories.destroy',$story->id)}}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
        @endif

        @if(!$story->finished && $story->sentences->last()->author_id != Auth::id())
            @if(array_key_exists(Auth::user()->id,$allUsers))
            <a href="{{route('sentences.create.special',$story->id)}}" class="btn btn-warning">Append</a>
            @elseif($story->open)
            <a href="{{route('sentences.create.special',$story->id)}}" class="btn btn-warning">Join and append</a>
            @endif
        @endif
    </div>
</div>

<h4 class="mt-3 card-title">Comments</h4>

@if(\Auth::user())
<div class="card mb-3">
    <div class="card-body">
        <form action="{{route('comments.store')}}" method="POST">
            @csrf
            <div class="form-group">
                <label for="text">Comment text</label>
                <textarea id="text" class="form-control" name="text" rows="3"
                    placeholder="Enter comment">{{old('text')}}</textarea>
                @if ($errors->has('text'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('text') }}</strong>
                </span>
                @endif
                <input name="story_id" value="{{$story->id}}" required hidden />
                <input name="user_id" value="{{\Auth::user()->id}}" required hidden />
            </div>
            <button type="submit" class="btn btn-primary">Comment</button>
        </form>
    </div>
</div>
@endif

@foreach($story->comments()->orderBy('created_at','desc')->get() as $comment)
    @component('components.comment',compact('comment'))
    @endcomponent
@endforeach

@endsection
