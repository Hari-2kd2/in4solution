<div class="row" hidden>
    <h3> Document 1</h3>
    <div class="col-md-4">
        <div class="form-group">
            <label>Document Title</label>
            <input type="text" class="form-control" name="document_title">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input class="form-control photo" id="document-file" accept="image/png, image/jpeg, application/pdf"
                name="document_file" type="file">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry"
                    placeholder="Document Expiry" name="document_expiry" type="text" value="">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <h3> Document 2</h3>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title2">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input class="form-control photo" id="document-file" accept="image/png, image/jpeg, application/pdf"
                name="document_file2" type="file">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry"
                    placeholder="Document Expiry" name="document_expiry2" type="text" value="">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <h3> Document 3</h3>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title3">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input class="form-control photo" id="document-file" accept="image/png, image/jpeg, application/pdf"
                name="document_file3" type="file">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry"
                    placeholder="Document Expiry" name="document_expiry3" type="text" value="">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <h3> Document 4</h3>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title4">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input class="form-control photo" id="document-file" accept="image/png, image/jpeg, application/pdf"
                name="document_file4" type="file">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry"
                    placeholder="Document Expiry" name="document_expiry4" type="text" value="">
            </div>
        </div>
    </div>
</div>

<div class="row" hidden>
    <h3> Document 5</h3>
    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Document Title</label>
            <input type="text" class="form-control" name="document_title5">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Upload Document</label>
            <input class="form-control photo" id="document-file" accept="image/png, image/jpeg, application/pdf"
                name="document_file5" type="file">
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="exampleInput">Expiry Date</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control dateField" readonly required id="document_expiry"
                    placeholder="Document Expiry" name="document_expiry5" type="text" value="">
            </div>
        </div>
    </div>
</div>

<br>


<h3 class="box-title" hidden>@lang('employee.educational_qualification')</h3>

<div class="education_qualification_append_div" hidden>
</div>

<div class="row" hidden>
    <div class="col-md-9"></div>
    <div class="col-md-3">
        <div class="form-group">
            <input id="addEducationQualification" type="button" class="form-control btn btn-success appendBtnColor"
                value="@lang('employee.add_educational_qualification')">
        </div>
    </div>
</div>

<h3 class="box-title" hidden>@lang('employee.professional_experience')</h3>

<hr>

<div class="experience_append_div" hidden>
</div>

<div class="row" hidden>
    <div class="col-md-9"></div>
    <div class="col-md-3">
        <div class="form-group"><input id="addExperience" type="button"
                class="form-control btn btn-success appendBtnColor" value="@lang('employee.add_professional_experience')"></div>
    </div>
</div>

<div class="form-actions">
    <div class="row">
        <div class="col-md-12 ">
            <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i>
                @lang('common.save')</button>
        </div>
    </div>
</div>


<div class="row_element1" style="display: none;">

    <input name="educationQualification_cid[]" type="hidden">

    <div class="row">

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.institute')<span class="validateRq">*</span></label>

                <select name="institute[]" class="form-control institute">

                    <option value="">--- @lang('common.please_select') ---</option>

                    <option value="Board">@lang('employee.board')</option>

                    <option value="University">@lang('employee.university')</option>

                </select>

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.board') / @lang('employee.university')<span
                        class="validateRq">*</span></label>

                <input type="text" name="board_university[]" class="form-control board_university"
                    id="board_university" placeholder="@lang('employee.board') / @lang('employee.university')">

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.degree')<span class="validateRq">*</span></label>

                <input type="text" name="degree[]" class="form-control degree required" id="degree"
                    placeholder="Example: B.Sc. Engr.(Bachelor of Science in Engineering)">

            </div>

        </div>

        <div class="col-md-3">

            <label for="exampleInput">@lang('employee.passing_year')<span class="validateRq">*</span></label>

            <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-calendar-o"></i></span>

                <input type="text" name="passing_year[]" class="form-control yearPicker required"
                    id="passing_year" placeholder="@lang('employee.passing_year')">

            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.result')</label>

                <select name="result[]" class="form-control result">

                    <option value="">--- @lang('common.please_select') ---</option>

                    <option value="First class">First class</option>

                    <option value="Second class">Second class</option>

                    <option value="Third class">Third class</option>

                </select>

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.gpa') / @lang('employee.cgpa')</label>

                <input type="text" name="cgpa[]" class="form-control cgpa" id="cgpa"
                    placeholder="Example: 5.00,4.63">

            </div>

        </div>

        <div class="col-md-3"></div>

        <div class="col-md-3">

            <div class="form-group">

                <input type="button" class="form-control btn btn-danger deleteEducationQualification appendBtnColor"
                    style="margin-top: 17px" value="@lang('common.delete')">

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

                <label for="exampleInput">@lang('employee.organization_name')<span class="validateRq">*</span></label>

                <input type="text" name="organization_name[]" class="form-control organization_name"
                    id="organization_name" placeholder="@lang('employee.organization_name')">

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.designation')<span class="validateRq">*</span></label>

                <input type="text" name="designation[]" class="form-control designation" id="designation"
                    placeholder="@lang('employee.designation')">

            </div>

        </div>

        <div class="col-md-3">

            <label for="exampleInput">@lang('common.from_date')<span class="validateRq">*</span></label>

            <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                <input type="text" name="from_date[]" class="form-control dateField" id="from_date"
                    placeholder="@lang('common.from_date')">

            </div>

        </div>

        <div class="col-md-3">

            <label for="exampleInput">@lang('common.to_date')<span class="validateRq">*</span></label>

            <div class="input-group">

                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>

                <input type="text" name="to_date[]" class="form-control dateField" id="to_date"
                    placeholder="@lang('common.to_date')">

            </div>

        </div>

    </div>



    <div class="row">

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.responsibility')<span class="validateRq">*</span></label>

                <textarea name="responsibility[]" class="form-control responsibility" placeholder="@lang('employee.responsibility')"
                    cols="30" rows="2"></textarea>

            </div>

        </div>

        <div class="col-md-3">

            <div class="form-group">

                <label for="exampleInput">@lang('employee.skill')<span class="validateRq">*</span></label>

                <textarea name="skill[]" class="form-control skill" placeholder="@lang('employee.skill')" cols="30"
                    rows="2"></textarea>

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

    <hr>

</div>
