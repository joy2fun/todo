<div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <h1 style="margin:0">{{ $headTitle ?: "Today's Tasks" }}</h1>
        <a href="/chiao" style="color:#839496; font-size:0.9rem">
            <x-filament::icon
            icon="heroicon-o-cog"
            style="width: 1.2rem; height: 1.2rem;"
        /></a>
    </div>

    <ul class="todo-list">
        @forelse ($this->todos as $todo)
            @php
                $progress = $todo->target_count > 0 ? round(min(($todo->completed_count / $todo->target_count) * 100, 100)) : 0;
                $isCompleted = $todo->status === 'completed';
            @endphp
            <li
                class="todo-item {{ $isCompleted ? 'completed' : '' }}"
                id="todo-{{ $todo->id }}"
                data-progress="{{ $progress }}"
                data-target="{{ $todo->target_count }}"
                data-completed="{{ $todo->completed_count }}"
            >
                <div class="todo-progress-bar" style="width: {{ $progress }}%"></div>
                <div class="todo-icon">
                    @if ($isCompleted)
                        <x-filament::icon icon="heroicon-o-check" style="width: 1.2rem; height: 1.2rem;" />
                    @else
                        &bull;
                    @endif
                </div>
                <div class="todo-item-content">
                    {{ $todo->habit->name ?? 'Unknown Habit' }}
                </div>

                @if (! $isCompleted)
                    <div class="tick-icon" onclick="activateSlide({{ $todo->id }})">
                            <x-filament::icon icon="heroicon-o-check" style="width: 1.5rem; height: 1.5rem;" />
                        </div>
                    <div class="confirm-text" onclick="confirmTodo({{ $todo->id }})">DONE</div>
                @endif
            </li>
        @empty
            <div class="empty-state">
                <div class="empty-state-icon">&#128203;</div>
                <p>No tasks for today. Enjoy your day!</p>
            </div>
        @endforelse
    </ul>
</div>

@script
<script>
    if (window.navigator.standalone) {
        var _firstVisible = true;

        document.addEventListener('visibilitychange', function() {
            if (_firstVisible) {
                _firstVisible = false;
                return;
            }
            if (!document.hidden) {
                setTimeout(function() { $wire.$refresh(); }, 100);
            }
        });
    }


    window.activateSlide = function(todoId) {
        var item = document.getElementById('todo-' + todoId);
        if (!item) return;

        item.classList.add('slide-active');

        var handler = function(e) {
            if (!item.contains(e.target)) {
                item.classList.remove('slide-active');
                document.removeEventListener('click', handler);
                item._clickOutsideHandler = null;
            }
        };

        item._clickOutsideHandler = handler;

        setTimeout(function() {
            document.addEventListener('click', handler);
        }, 100);
    };

    window.confirmTodo = function(todoId) {
        var item = document.getElementById('todo-' + todoId);
        if (!item) return;

        createFireworks(item);

        var tickIcon = item.querySelector('.tick-icon');
        var confirmText = item.querySelector('.confirm-text');

        if (tickIcon) {
            tickIcon.style.opacity = '0';
            tickIcon.style.visibility = 'hidden';
            tickIcon.style.pointerEvents = 'none';
        }
        if (confirmText) {
            confirmText.style.opacity = '0';
            confirmText.style.visibility = 'hidden';
            confirmText.style.pointerEvents = 'none';
        }

        if (item._clickOutsideHandler) {
            document.removeEventListener('click', item._clickOutsideHandler);
            item._clickOutsideHandler = null;
        }

        var bar = item.querySelector('.todo-progress-bar');
        var startPct = parseFloat(item.dataset.progress) || 0;
        var targetCount = parseInt(item.dataset.target) || 1;
        var completedCount = parseInt(item.dataset.completed) || 0;
        var endPct = Math.round(((completedCount + 1) / targetCount) * 100);
        if (endPct > 100) endPct = 100;
        var duration = 1200;
        var startTime = performance.now();

        bar.classList.add('animate');

        function animateProgress(now) {
            var elapsed = now - startTime;
            var t = Math.min(elapsed / duration, 1);
            var eased = 1 - Math.pow(1 - t, 3);
            var pct = startPct + (endPct - startPct) * eased;

            bar.style.width = pct + '%';

            if (t < 1) {
                requestAnimationFrame(animateProgress);
            } else {
                item.classList.add('completed');

                setTimeout(function() {
                    $wire.markCompleted(todoId).then(function() {
                        $wire.refreshTodos();
                    });
                }, 500);
            }
        }

        requestAnimationFrame(animateProgress);
    };

    function createFireworks(container) {
        var fireworksContainer = document.createElement('div');
        fireworksContainer.className = 'fireworks-container';

        var sparkle = document.createElement('div');
        sparkle.className = 'firework-sparkle';
        sparkle.style.width = '40px';
        sparkle.style.height = '40px';
        sparkle.style.left = 'calc(50% - 20px)';
        sparkle.style.top = 'calc(50% - 20px)';
        fireworksContainer.appendChild(sparkle);

        var colorPalettes = [
            ['#FF595E', '#FFCA3A', '#8AC926', '#1982C4', '#6A4C93'],
            ['#FF6B6B', '#4ECDC4', '#FFE66D', '#FF9A3C', '#A8D8EA'],
            ['#FF3864', '#2DE2E6', '#FFE377', '#2B2D42', '#F05D5E'],
            ['#EF476F', '#FFD166', '#06D6A0', '#118AB2', '#073B4C']
        ];

        var colors = colorPalettes[Math.floor(Math.random() * colorPalettes.length)];

        for (var i = 0; i < 50; i++) {
            var particle = document.createElement('div');
            particle.className = 'firework-particle';

            var size = 2 + Math.random() * 8;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';

            var startOffsetX = (Math.random() - 0.5) * 30;
            var startOffsetY = (Math.random() - 0.5) * 30;

            particle.style.left = 'calc(50% + ' + startOffsetX + 'px)';
            particle.style.top = 'calc(50% + ' + startOffsetY + 'px)';

            var angle = Math.random() * Math.PI * 2;
            var distance = 30 + Math.random() * 70;
            var gravity = 0.5 + Math.random();
            var dx = Math.cos(angle) * distance;
            var dy = Math.sin(angle) * distance + gravity * distance * 0.3;
            var finalScale = 0.3 + Math.random() * 1.2;

            particle.style.setProperty('--dx', dx + 'px');
            particle.style.setProperty('--dy', dy + 'px');
            particle.style.setProperty('--final-scale', finalScale);

            var color = colors[Math.floor(Math.random() * colors.length)];
            particle.style.backgroundColor = color;
            particle.style.boxShadow = '0 0 8px ' + color + ', 0 0 12px rgba(255,255,255,0.5)';
            particle.style.animationDelay = (Math.random() * 0.2) + 's';
            particle.style.animationDuration = (0.6 + Math.random() * 0.4) + 's';

            fireworksContainer.appendChild(particle);
        }

        for (var j = 0; j < 10; j++) {
            (function(index) {
                setTimeout(function() {
                    var extraSparkle = document.createElement('div');
                    extraSparkle.className = 'firework-sparkle';
                    var sparkleSize = 20 + Math.random() * 30;
                    extraSparkle.style.width = sparkleSize + 'px';
                    extraSparkle.style.height = sparkleSize + 'px';
                    extraSparkle.style.left = (20 + Math.random() * 60) + '%';
                    extraSparkle.style.top = (20 + Math.random() * 60) + '%';

                    var color = colors[Math.floor(Math.random() * colors.length)];
                    extraSparkle.style.background = 'radial-gradient(circle at center, ' + color + '80 0%, transparent 70%)';

                    fireworksContainer.appendChild(extraSparkle);

                    setTimeout(function() {
                        if (extraSparkle.parentNode) extraSparkle.remove();
                    }, 500);
                }, index * 50 + 100);
            })(j);
        }

        container.appendChild(fireworksContainer);

        setTimeout(function() {
            if (fireworksContainer.parentNode) fireworksContainer.remove();
        }, 1500);
    }
</script>
@endscript
