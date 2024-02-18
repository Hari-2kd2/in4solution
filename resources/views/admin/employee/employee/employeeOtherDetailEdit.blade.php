<input class="form-control  delete_education_qualifications_cid" id="delete_education_qualifications_cid"
    name="delete_education_qualifications_cid" type="hidden" value="">
<input class="form-control  delete_experiences_cid" id="delete_experiences_cid" name="delete_experiences_cid" type="hidden"
    value="">

<div class="col-md-3" hidden>
    <div class="form-group">
        <label for="work_shift_id">@lang('work_shift.work_shift_name')<span class="validateRq">*</span></label>
        <select name="work_shift_id" id="work_shift_id" class="form-control work_shift_id">
            <option value="">--- @lang('common.please_select') ---</option>
            @foreach ($workShiftList as $value)
                <option value="{{ $value->work_shift_id }}"
                    @if ($value->work_shift_id == $editModeData->work_shift_id) {{ 'selected' }} @endif>
                    {{ $value->shift_name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="col-md-3" hidden>
    <div class="form-group">
        <label for="pay_grade_id">@lang('employee.montly_paygrade')<span class="validateRq">*</span></label>
        <select name="pay_grade_id" id="pay_grade_id" class="form-control pay_grade_id">
            <option value="">--- @lang('common.please_select') ---</option>
            @foreach ($payGradeList as $value)
                <option value="{{ $value->pay_grade_id }}" @if ($value->pay_grade_id == $editModeData->pay_grade_id) {{ 'selected' }} @endif>
                    {{ $value->pay_grade_name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="col-md-3" hidden>
    <div class="form-group">
        <label for="hourly_salaries_id">@lang('employee.hourly_paygrade')<span class="validateRq">*</span></label>
        <select name="hourly_salaries_id" id="hourly_salaries_id" class="form-control hourly_pay_grade_id">
            <option value="">--- @lang('common.please_select') ---</option>
            @foreach ($hourlyPayGradeList as $value)
                <option value="{{ $value->hourly_salaries_id }}"
                    @if ($value->hourly_salaries_id == $editModeData->hourly_salaries_id) {{ 'selected' }} @endif>
                    {{ $value->hourly_grade }}</option>
            @endforeach
        </select>
    </div>
</div>


<div class="col-md-3" hidden>
    <div class="form-group">
        <label for="esi_card_number">@lang('employee.esi_card_number')</label>
        <input class="form-control esi_card_number" id="esi_card_number" placeholder="@lang('employee.esi_card_number')" name="esi_card_number" type="text" value="{{ $editModeData->esi_card_number }}">
    </div>
</div>

<div class="col-md-3" hidden>
    <div class="form-group">
        <label for="pf_account_number">@lang('employee.pf_account_number')</label>
        <input class="form-control pf_account_number" id="pf_account_number" placeholder="@lang('employee.pf_account_number')" name="pf_account_number" type="text" value="{{ $editModeData->pf_account_number }}">
    </div>
</div>

<div class="row" hidden>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title"
                value="{{ $editModeData->document_title }}">
        </div>
    </div>
    <div class="col-md-4" hidden>
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input type="hidden" name="document_oldfile" value="{{ $editModeData->document_name }}">
            <input class="form-control photo" id="document-file" accept="image/png, image/jpeg, application/pdf"
                name="document_file" type="file" value="0">
        </div>
    </div>
    <div class="col-md-4" hidden>
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry"
                    placeholder="Document Expiry" name="document_expiry" type="text"
                    value="{{ $editModeData->document_expiry }}">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title2"
                value="{{ $editModeData->document_title2 }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input type="hidden" name="document_oldfile2" value="{{ $editModeData->document_name2 }}">
            <input class="form-control photo" id="document-file2" accept="image/png, image/jpeg, application/pdf"
                name="document_file2" type="file" value="0">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry2"
                    placeholder="Document Expiry" name="document_expiry2" type="text"
                    value="{{ $editModeData->document_expiry2 }}">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title3"
                value="{{ $editModeData->document_title3 }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input type="hidden" name="document_oldfile3" value="{{ $editModeData->document_name3 }}">
            <input class="form-control photo" id="document-file3" accept="image/png, image/jpeg, application/pdf"
                name="document_file3" type="file" value="0">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry3"
                    placeholder="Document Expiry" name="document_expiry3" type="text"
                    value="{{ $editModeData->document_expiry3 }}">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title4"
                value="{{ $editModeData->document_title4 }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input type="hidden" name="document_oldfile4" value="{{ $editModeData->document_name4 }}">
            <input class="form-control photo" id="document-file4" accept="image/png, image/jpeg, application/pdf"
                name="document_file4" type="file" value="0">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry4"
                    placeholder="Document Expiry" name="document_expiry4" type="text"
                    value="{{ $editModeData->document_expiry4 }}">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title5"
                value="{{ $editModeData->document_title5 }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input type="hidden" name="document_oldfile5" value="{{ $editModeData->document_name5 }}">
            <input class="form-control photo" id="document-file5" accept="image/png, image/jpeg, application/pdf"
                name="document_file5" type="file" value="0">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry5"
                    placeholder="Document Expiry" name="document_expiry5" type="text"
                    value="{{ $editModeData->document_expiry5 }}">
            </div>
        </div>
    </div>
</div>

<br hidden>

<h3 class="box-title" hidden>@lang('employee.educational_qualification')</h3>

<hr hidden>

<div class="education_qualification_append_div" hidden>

    @if (isset($editModeData) && count($educationQualificationEditModeData) > 0)
        @foreach ($educationQualificationEditModeData as $educationQualificationValue)
            <div class="education_qualification_row_element">

                <input class="educationQualification_cid" id="educationQualification_cid"
                    name="educationQualification_cid[]" type="hidden"
                    value="{{ $educationQualificationValue->employee_education_qualification_id }}">

                <div class="row">

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.institute')<span class="validateRq">*</span></label>

                            <select name="institute[]" class="form-control institute">

                                <option value="">--- @lang('common.please_select') ---</option>

                                <option value="Board" @if ($educationQualificationValue->institute == 'Board') {{ 'selected' }} @endif>
                                    @lang('employee.board')</option>

                                <option value="University"
                                    @if ($educationQualificationValue->institute == 'University') {{ 'selected' }} @endif>
                                    @lang('employee.university')</option>

                            </select>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.board') /
                                @lang('employee.university')<span class="validateRq">*</span></label>

                            <input type="text" name="board_university[]" class="form-control board_university"
                                id="board_university" placeholder="@lang('employee.board') / @lang('employee.university')"
                                value="{{ $educationQualificationValue->board_university }}">

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.degree')<span class="validateRq">*</span></label>

                            <input type="text" name="degree[]" class="form-control degree required"
                                id="degree" placeholder="Example: B.Sc. Engr.(Bachelor of Science in Engineering)"
                                value="{{ $educationQualificationValue->degree }}">

                        </div>

                    </div>

                    <div class="col-md-3">

                        <label for="exampleInput">@lang('employee.passing_year')<span class="validateRq">*</span></label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-calendar-o"></i></span>

                            <input type="text" name="passing_year[]" class="form-control yearPicker required"
                                id="passing_year" placeholder="@lang('employee.passing_year')"
                                value="{{ $educationQualificationValue->passing_year }}">

                        </div>

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.result')</label>

                            <select name="result[]" class="form-control result">

                                <option value="">--- @lang('common.please_select') ---</option>

                                <option value="First class"
                                    @if ($educationQualificationValue->result == 'First class') {{ 'selected' }} @endif>
                                    First class</option>

                                <option value="Second class"
                                    @if ($educationQualificationValue->result == 'Second class') {{ 'selected' }} @endif>
                                    Second class</option>

                                <option value="Third class"
                                    @if ($educationQualificationValue->result == 'Third class') {{ 'selected' }} @endif>
                                    Third class</option>

                            </select>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.gpa') /
                                @lang('employee.cgpa')</label>

                            <input type="text" name="cgpa[]" class="form-control cgpa" id="cgpa"
                                placeholder="Example: 5.00,4.63" value="{{ $educationQualificationValue->cgpa }}">

                        </div>

                    </div>

                    <div class="col-md-3"></div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <input type="button"
                                class="form-control btn btn-danger deleteEducationQualification appendBtnColor"
                                style="margin-top: 17px" value="@lang('common.delete')">

                        </div>

                    </div>

                </div>

                <hr hidden>

            </div>
        @endforeach
    @endif

</div>

<div class="row" hidden>

    <div class="col-md-9"></div>

    <div class="col-md-3">
        <div class="form-group"><input id="addEducationQualification" type="button"
                class="form-control btn btn-success appendBtnColor" value="@lang('employee.add_educational_qualification')"></div>
    </div>

</div>

<h3 class="box-title" hidden>@lang('employee.professional_experience')</h3>

<hr hidden>

<div class="experience_append_div" hidden>

    @if (isset($editModeData) && count($experienceEditModeData) > 0)
        @foreach ($experienceEditModeData as $experienceValue)
            <div class="experience_row_element">

                <input class="employee_experience_id" id="employee_experience_id" name="employeeExperience_cid[]"
                    type="hidden" value="{{ $experienceValue->employee_experience_id }}">

                <div class="row">

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.organization_name')<span class="validateRq">*</span></label>

                            <input type="text" name="organization_name[]" class="form-control organization_name"
                                id="organization_name" placeholder="@lang('employee.organization_name')"
                                value="{{ $experienceValue->organization_name }}">

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.designation')<span class="validateRq">*</span></label>

                            <input type="text" name="designation[]" class="form-control designation"
                                id="designation" placeholder="@lang('employee.designation')"
                                value="{{ dateConvertDBtoForm($experienceValue->designation) }}">

                        </div>

                    </div>

                    <div class="col-md-3">

                        <label for="exampleInput">@lang('common.from_date')<span class="validateRq">*</span></label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                            <input type="text" name="from_date[]" class="form-control dateField" id="from_date"
                                placeholder="@lang('common.from_date')"
                                value="{{ dateConvertDBtoForm($experienceValue->from_date) }}">

                        </div>

                    </div>

                    <div class="col-md-3">

                        <label for="exampleInput">@lang('common.to_date')<span class="validateRq">*</span></label>

                        <div class="input-group">

                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                            <input type="text" name="to_date[]" class="form-control dateField" id="to_date"
                                placeholder="@lang('common.to_date')"
                                value="{{ dateConvertDBtoForm($experienceValue->to_date) }}">

                        </div>

                    </div>

                </div>



                <div class="row">

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.responsibility')<span class="validateRq">*</span></label>

                            <textarea name="responsibility[]" class="form-control responsibility" placeholder="@lang('employee.responsibility')"
                                cols="30" rows="2" required>{{ $experienceValue->responsibility }}</textarea>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <label for="exampleInput">@lang('employee.skill')<span class="validateRq">*</span></label>

                            <textarea name="skill[]" class="form-control skill" placeholder="@lang('employee.skill')" cols="30" rows="2">{{ $experienceValue->skill }}</textarea>

                        </div>

                    </div>

                    <div class="col-md-3"></div>

                    <div class="col-md-3">

                        <div class="form-group">

                            <input type="button" class="form-control btn btn-danger deleteExperience appendBtnColor"
                                style="margin-top: 17px" value="@lang('common.delete')">

                        </div>

                    </div>

                </div>

                <hr hidden>

            </div>
        @endforeach
    @endif

</div>

<div class="row" hidden>

    <div class="col-md-9"></div>

    <div class="col-md-3">
        <div class="form-group"><input id="addExperience" type="button"
                class="form-control btn btn-success appendBtnColor" value="@lang('employee.add_professional_experience')">
        </div>
    </div>

</div>
<h3 class="box-title" hidden>@lang('employee.conntected_device')</h3>
<hr>
<div class="connected_device" hidden>

    <div class="form-group">
        <div class="col-md-12" style="border:1px solid lightgrey;min-height: 250px;">
            @foreach ($device_list as $device)
                @php
                    $access = AccessControl::where('employee', $editModeData->employee_id)
                        ->where('device', $device->id)
                        ->first();
                @endphp
                @if ($access)
                    <div class="col-md-3">
                        <div class="checkbox checkbox-info"><input class="inputCheckbox" type="checkbox"
                                id="inlineCheckbox{{ $device->id }}" checked="" name="device_id[]"
                                value="{{ $device->id }}">
                            <label for="inlineCheckbox{{ $device->id }}">{!! $device->name !!}
                                ({{ $device->model }})
                            </label>
                        </div>

                    </div>
                @else
                    <div class="col-md-3">
                        <div class="checkbox checkbox-info"><input class="inputCheckbox" type="checkbox"
                                id="inlineCheckbox{{ $device->id }}" name="device_id[]"
                                value="{{ $device->id }}">
                            <label for="inlineCheckbox{{ $device->id }}">{!! $device->name !!}
                                ( {{ $device->model }} )</label>
                        </div>

                    </div>
                @endif
            @endforeach
        </div>
    </div>

</div>
<br hidden><br hidden>

<div class="row_element1" style="display: none;">
    <input name="educationQualification_cid[]" type="hidden">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="institute">@lang('employee.institute')<span class="validateRq">*</span></label>
                <select name="institute[]" id="institute" class="form-control institute">
                    <option value="">--- @lang('common.please_select') ---</option>
                    <option value="Board">@lang('employee.board')</option>
                    <option value="University">@lang('employee.university')</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="board_university">@lang('employee.board') / @lang('employee.university')<span class="validateRq">*</span></label>
                <input type="text" name="board_university[]" class="form-control board_university" id="board_university" placeholder="@lang('employee.board') / @lang('employee.university')">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="degree">@lang('employee.degree')<span class="validateRq">*</span></label>
                <input type="text" name="degree[]" class="form-control degree required" id="degree" placeholder="Example: B.Sc. Engr.(Bachelor of Science in Engineering)">
            </div>
        </div>

        <div class="col-md-3">
            <label for="passing_year">@lang('employee.passing_year')<span class="validateRq">*</span></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar-o"></i></span>
                <input type="text" name="passing_year[]" class="form-control yearPicker required" id="passing_year" placeholder="@lang('employee.passing_year')">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="result">@lang('employee.result')</label>
                <select name="result[]" id="result" class="form-control result">
                    <option value="">--- @lang('common.please_select') ---</option>
                    <option value="First class">First class</option>
                    <option value="Second class">Second class</option>
                    <option value="Third class">Third class</option>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="cgpa">@lang('employee.gpa') / @lang('employee.cgpa')</label>
                <input type="text" name="cgpa[]" class="form-control cgpa" id="cgpa" placeholder="Example: 5.00,4.63">
            </div>
        </div>

        <div class="col-md-3"></div>

        <div class="col-md-3">
            <div class="form-group">
                <input type="button" class="form-control btn btn-danger deleteEducationQualification appendBtnColor" style="margin-top: 17px" value="@lang('common.delete')">
            </div>
        </div>
    </div>
    <hr>
</div>

<div class="row_element2" style="display: none;">
    <input name="employeeExperience_cid[]" type="hidden">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="organization_name">@lang('employee.organization_name')<span class="validateRq">*</span></label>
                <input type="text" name="organization_name[]" class="form-control organization_name" id="organization_name" placeholder="@lang('employee.organization_name')">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="designation">@lang('employee.designation')<span class="validateRq">*</span></label>
                <input type="text" name="designation[]" class="form-control designation" id="designation" placeholder="@lang('employee.designation')">
            </div>
        </div>

        <div class="col-md-3">
            <label for="from_date">@lang('common.from_date')<span class="validateRq">*</span></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="from_date[]" class="form-control dateField" id="from_date" placeholder="@lang('common.from_date')">
            </div>
        </div>

        <div class="col-md-3">
            <label for="to_date">@lang('common.to_date')<span class="validateRq">*</span></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" name="to_date[]" class="form-control dateField" id="to_date" placeholder="@lang('common.to_date')">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="responsibility">@lang('employee.responsibility')<span class="validateRq">*</span></label>
                <textarea name="responsibility[]" id="responsibility" class="form-control responsibility" placeholder="@lang('employee.responsibility')" cols="30" rows="2"></textarea>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="skill">@lang('employee.skill')<span class="validateRq">*</span></label>
                <textarea name="skill[]" id="skill" class="form-control skill" placeholder="@lang('employee.skill')" cols="30" rows="2"></textarea>
            </div>
        </div>

        <div class="col-md-3"></div>
        <div class="col-md-3">
            <div class="form-group">
                <input type="button" class="form-control btn btn-danger deleteExperience appendBtnColor" style="margin-top: 17px" value="@lang('common.delete')">
            </div>
        </div>
    </div>
    <hr>
</div>
