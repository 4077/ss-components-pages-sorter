<div class="{__NODE_ID__}" instance="{__INSTANCE__}">

    <div class="section">
        <div class="row">
            <div class="label">По наличию в выбранной группе</div>
            <div class="control">
                {BY_GROUP_TOGGLE}
            </div>
        </div>

        <!-- selected_enabled -->
        <div class="row l2">
            <div class="label">Минимальное</div>
            <div class="control">
                {INPUT}
            </div>
        </div>
        <!-- / -->

        <div class="row">
            <div class="label">По наличию в остальных группах</div>
            <div class="control">
                {BY_OTHERS_TOGGLE}
            </div>
        </div>

        <!-- others_enabled -->
        <div class="row l2">
            <div class="label">Минимальное</div>
            <div class="control">
                {INPUT}
            </div>
        </div>
        <!-- / -->
    </div>

    <div class="render">
        <div class="render_button">
            <div class="idle">
                <div class="icon fa fa-angle-double-right"></div>
            </div>
            <div class="proc">
                <div class="progress">
                    <div class="bar"></div>
                    <div class="info">
                        <span class="status"></span>
                        <span class="position"></span>
                        <span class="percent"></span>
                    </div>

                    <div class="break_button">прервать</div>
                </div>
            </div>
        </div>
    </div>

</div>
