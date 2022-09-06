class AdSchedule {

    id = "";
    isDragging = false;
    enable = false;
    selection = {};

    sortNumbers = (a, b) => a - b;

    setSelection = (selection, enable) => {
        // make copies before sorting
        const coords = {
            x: selection["x"].slice().sort(this.sortNumbers), y: selection["y"].slice().sort(this.sortNumbers)
        }
        // Only get relevant rows within range
        const rows = $("#" + this.id + ">tbody tr").slice(coords["y"][0], coords["y"][1] + 1);
        let cells = $();
        // In each relevant row, get the relevant cells
        rows.each(function (i, el) {
            cells = cells.add($(el).children("td").slice(coords["x"][0], coords["x"][1] + 1));
        });
        if (enable) {
            cells.addClass("active");
            cells.removeClass("paused");
        } else {
            cells.removeClass("active");
            cells.addClass("paused");
        }

    }

    addListener = () => {
        let thisSchedule = this;
        $("#" + this.id).on("mousedown", "td", function () {
            // Start dragging
            thisSchedule.isDragging = true;
            thisSchedule.enable = !$(this).hasClass("active");

            const $this = $(this);
            thisSchedule.selection["x"] = [$this.index(), $this.index()];
            thisSchedule.selection["y"] = [$this.parent("tr").index(), $this.parent("tr").index()];
            thisSchedule.setSelection(thisSchedule.selection, thisSchedule.enable);
            return false;
        }).on("mouseover", "td", function () {
            if (thisSchedule.isDragging) {
                const $this = $(this);
                thisSchedule.selection["x"][1] = $this.index();
                thisSchedule.selection["y"][1] = $this.parent("tr").index();
                thisSchedule.setSelection(thisSchedule.selection, thisSchedule.enable);
            }
            return false;
        }).on("mouseup", "td", function () {
            thisSchedule.isDragging = false;
            return false;
        }).on("mouseleave", function () {
            thisSchedule.isDragging = false;
            let sendMe = [];
            $("#" + this.id + " tr").each(function (indexTr) {
                $(this).find("td").each(function (indexTd) {
                    if (typeof sendMe[indexTr + 1] === 'undefined') {
                        sendMe[indexTr + 1] = [];
                    }
                    if ($(this).hasClass("active")) {
                        sendMe[indexTr + 1][indexTd] = true;
                    }
                })
            });
            $("." + this.id + "_schedule").val(JSON.stringify(sendMe));
            return false;
        });
    }

    fillFromMainSchedule = function () {
        try {
            let values = JSON.parse($(".mainSchedule_schedule").val());
            $("#" + this.id + " tr").each(function (trIndex) {
                trIndex += 1;
                $(this).find('td').each(function (tdIndex) {
                    if (values[trIndex] && values[trIndex][tdIndex]) {
                        $(this).removeClass("paused").addClass("active");
                    }
                });
            });
            $("." + this.id + "_schedule").val(JSON.stringify(values));
        } catch (e) {
        }
    }

    constructor(id) {
        this.id = id;
        if (/complete|interactive|loaded/.test(document.readyState)) {
            this.addListener();
        } else {
            document.addEventListener('DOMContentLoaded', this.addListener, false);
        }

    }
}
