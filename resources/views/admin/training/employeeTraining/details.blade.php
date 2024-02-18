@extends('admin.master')
@section('content')
@section('title')
@lang('training.employee_training_details')
@endsection

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
            <a href="{{ route('trainingInfo.index') }}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i class="fa fa-list-ul" aria-hidden="true"></i> @lang('training.view_employee_training') </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;
                            <strong>{{ session()->get('success') }}</strong>
                        </div>
                        @endif

                        @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="glyphicon glyphicon-remove"></i>&nbsp;
                            <strong>{!! session()->get('error') !!}</strong>

                        </div>
                        @endif
                        @if ($errors->any())
                        <div class="alert alert-danger alert-block alert-dismissable">
                            <ul>
                                <button type="button" class="close" data-dismiss="alert">x</button>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="col-lg-offset-2 col-md-8">
                            <div class="white-box">
                                <div class="comment-center p-t-10">
                                    <div class="model-body">
                                        <div>
                                            <p><b>Subject:</b>
                                                {{ $result->subject }}
                                            </p>
                                            <p><b>Description:</b> <br>
                                                {{ $result->description }}
                                            </p>
                                        </div>
                                        @if($result->video_html)
                                        <br>
                                        <h3 class="box-header">Training Video</h3>
                                        <div class="row" style="padding: 0px 20px 0 0;">
                                            <div class="col-md-6">
                                                @if ($result->training_duration)
                                                <p class="pull-left text-cetner" style="width: fit-content">
                                                    Training Duration: <span id="playbackTime">{{ $result->training_duration }}</span>
                                                </p>
                                                @endif
                                            </div>
                                            @if ($is_read)
                                            <button type="button" class="col-md-6 btn btn-xs btn-danger pull-right" style="width: fit-content">Read at
                                                {{ date('d M Y h:i A', strtotime($is_read->created_at)) }}</button>
                                            @else
                                            <a class="pull-right" href="{{ route('training.read', $result->training_info_id) }}">
                                                <button type="button" class="col-md-6 btn btn-xs btn-primary" style="width: fit-content">Mark as
                                                    Read</button>
                                            </a>
                                            @endif
                                        </div>
                                        {!! $result->video_html !!}
                                        @endif


                                        @if ($result->certificate != '')
                                        <br><br>
                                        <h3 class="box-header">Document</h3>
                                        <div class="model-contnet">


                                            <div>
                                                @php
                                                if ($result->certificate != '') {
                                                $info = new SplFileInfo($result->certificate);
                                                $extension = $info->getExtension();

                                                if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg' || $extension === 'PNG' || $extension === 'JPG' || $extension === 'JPEG') {
                                                echo '<img src="' . asset('uploads/employeeTrainingCertificate/' . $result->certificate) . '" width="100%">';
                                                } else {
                                                echo '<embed src="' . asset('uploads/employeeTrainingCertificate/' . $result->certificate) . '" width="100%" height="400" />';
                                                }
                                                }
                                                @endphp
                                            </div>

                                        </div>

                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('page_scripts')
<script>
    const video = document.getElementById('videoPlayer');
    const playbackTimeElement = document.getElementById('playbackTime');
    const playbackElement = document.getElementById('playback');

    video.addEventListener('timeupdate', () => {
        const currentTime = video.currentTime;
        const minutes = Math.floor(currentTime / 60);
        const seconds = Math.floor(currentTime % 60);
        const formattedTime = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        playbackTimeElement.textContent = formattedTime;
        // playbackElement.textContent = formattedTime;
        //    (formattedTime);
        $('#playback').val(formattedTime);
    });
</script>
@endsection