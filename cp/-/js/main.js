// head {
var __nodeId__ = "ss_components_pagesSorter_cp__main";
var __nodeNs__ = "ss_components_pagesSorter_cp";
// }

(function (__nodeNs__, __nodeId__) {
    $.widget(__nodeNs__ + "." + __nodeId__, $.ewma.node, {
        options: {},

        __create: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            w.bind();
            w.bindEvents();

            w.handleRenderXPid();
        },

        bindEvents: function () {
            var w = this;
            var o = w.options;

            w.e('ss/components/pagesSorter/renderStart', function (data) {
                w.handleRenderProc(data.xpid);
            });
        },

        bind: function () {
            var w = this;
            var o = w.options;
            var $w = w.element;

            var updateStringValue = function ($input) {
                w.r('updateStringValue', {
                    path:  $input.attr("path"),
                    value: $input.val()
                });
            };

            $("input[path]", $w).rebind("blur cut paste", function () {
                updateStringValue($(this));
            });

            $("input[path]", $w).rebind("keyup", function (e) {
                if (e.which === 13) {
                    updateStringValue($(this));
                }
            });

            //

            var $renderButton = $(".render_button", $w);

            $renderButton.on("click", function (e) {
                var sync = e.ctrlKey;

                if (sync) {
                    w.r('render', {
                        sync: true
                    });
                } else {
                    w.r('render', {}, false, function (data) {
                        w.handleRenderProc(data.xpid);
                    });
                }
            });
        },

        savedHighlight: function (path) {
            var $field = $("input[path='" + path + "']", this.element);

            $field.removeClass("updating").addClass("saved");

            setTimeout(function () {
                $field.removeClass("saved");
            }, 1000);
        },

        //
        // render proc
        //

        handleRenderXPid: function () {
            var w = this;
            var o = w.options;

            if (o.renderXPid) {
                w.handleRenderProc(o.renderXPid);
            }
        },

        handleRenderProc: function (xpid) {
            var w = this;
            var o = w.options;
            var $w = w.element;

            var $renderButton = $(".render_button", $w);
            var $idle = $(".idle", $renderButton);
            var $proc = $(".proc", $renderButton);
            var $bar = $(".bar", $proc);
            var $status = $(".status", $proc);
            var $position = $(".position", $proc);
            var $percent = $(".percent", $proc);
            var $breakButton = $(".break_button", $proc);

            $idle.hide();
            $proc.css({display: 'flex'});

            $bar.css({
                width: '0%'
            });

            var proc = ewma.proc(xpid);

            var prevPercent = 0;

            proc.loop(function (progress, output, errors) {
                if (output.status) {
                    $status.html(output.status);
                    $position.html('');
                    $percent.html('');
                } else {
                    $status.html('');
                    $position.html(progress.current + '/' + progress.total);
                    $percent.html(progress.percent_ceil + '%');
                }

                $bar.toggleClass("no_transition", progress.percent < prevPercent);

                $bar.css({
                    width: progress.percent + '%'
                });

                prevPercent = progress.percent;
            });

            proc.terminate(function (output, errors) {
                $idle.show();
                $proc.hide();
            });

            $breakButton.click(function (e) {
                proc.break();

                e.stopPropagation();
            });
        }
    });
})(__nodeNs__, __nodeId__);
