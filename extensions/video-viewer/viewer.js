function addVideoViewer(ev) {
    var video = this;
    var cloned = video.cloneNode(true);
    cloned.removeAttribute('controls');
    cloned.addEventListener('loadedmetadata', function() {
        cloned.loop = 'loop';
        cloned.currentTime = video.currentTime;
        cloned.playbackRate = video.playbackRate;
        cloned.play();
        cloned.addEventListener('timeupdate', function() {
            updateTime(time, cloned, player);
        });
        var dialog = document.createElement('div');
        dialog.classList.add('video-player-modal-holder');
        var player = document.createElement('div');
        player.classList.add('video-player');
        const holder = document.createElement('div');
        holder.classList.add('video-holder');
        holder.appendChild(cloned);
        player.appendChild(holder);

        var sliderHolder = document.createElement('div');
        sliderHolder.classList.add('slider-container');
        var slider = document.createElement('div');
        slider.classList.add('slider');
        var time = document.createElement('div');

        time.classList.add('time');
        time.textContent = `${Math.round(cloned.currentTime).toHHMMSS()}/${Math.round(cloned.duration).toHHMMSS()}`;
        
        slider.appendChild(time);
        var actualSlider = document.createElement('label');
        actualSlider.classList.add('actual-slider');
        const label = document.createElement('span');
        label.classList.add('hidden2eyes');
        label.textContent = "Time:";
        actualSlider.appendChild(label);
        var range = document.createElement('input');
        range.type = 'range';
        range.min = 0;
        range.step = 0.1;
        range.max = cloned.duration;
        range.value = cloned.currentTime;
        range.addEventListener('input', function() {
            cloned.currentTime = this.value;
        });
        actualSlider.appendChild(range);
        slider.appendChild(actualSlider);
        sliderHolder.appendChild(slider);

        player.appendChild(sliderHolder);
        const controls = document.createElement('div');
        controls.classList.add('controls');
        buttons = [
            {
                "text": "Pause",
                "onclick": function() { 
                    if (this.textContent === 'Pause') {
                        this.textContent = 'Play';
                        cloned.pause();
                    } else {
                        this.textContent = 'Pause';
                        cloned.play();
                    }
                }
            },
            {
                "text": "Speed",
                "onclick": function() {
                    const speed = document.createElement('form');
                    speed.classList.add('video-player');
                    speed.classList.add('setting');
                    speed.action = 'javascript:;';
                    speed.addEventListener('submit', function() {
                        cloned.playbackRate = speed.querySelector('input').value;
                        video.playbackRate = cloned.playbackRate;
                        speed.parentNode.removeChild(speed);
                    });
                    const label = document.createElement('label');
                    label.appendChild(document.createTextNode("Playback rate:" + String.fromCharCode(160)));
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.required = 'required';
                    input.value = cloned.playbackRate;
                    input.step = 0.1;
                    label.appendChild(input);
                    speed.appendChild(label);
                    speed.appendChild(document.createElement('br'));
                    const submit = document.createElement('button');
                    submit.textContent = 'Set playback rate';
                    speed.appendChild(submit);
                    speed.appendChild(document.createTextNode("\u00a0"));
                    const cancel = document.createElement('button');
                    cancel.textContent = 'Cancel';
                    cancel.addEventListener('click', function() {
                        speed.parentNode.removeChild(speed);
                    })
                    speed.appendChild(cancel);
                    document.body.appendChild(speed);
                }
            },
            {
                "text": "Close",
                "onclick": function() {
                    video.currentTime = cloned.currentTime;
                    dialog.parentNode.removeChild(dialog);
                }
            },
        ];
        buttons.forEach(function(button) {
            const b = document.createElement('button');
            b.textContent = button.text;
            b.addEventListener('click', button.onclick);
            controls.appendChild(b);
        })
        player.appendChild(controls);
        dialog.appendChild(player);
        document.body.appendChild(dialog);
    });
}
function videoViewerStart() {
    Array.from(document.querySelectorAll('video')).forEach(function(item) {
        item.removeAttribute('controls');
        item.addEventListener('click', addVideoViewer);
    });
}
addEventListener('DOMContentLoaded', videoViewerStart);

// https://stackoverflow.com/a/6313008/15578194, CC BY-SA 4.0
Number.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10);
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds;
}
function updateTime(time, cloned, player) {
    time.textContent = `${Math.round(cloned.currentTime).toHHMMSS()}/${Math.round(cloned.duration).toHHMMSS()}`;
    player.querySelector('[type=range]').value = cloned.currentTime;
}