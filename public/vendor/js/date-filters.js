// resources/js/shared/date-filters.js
// Requiere jQuery, moment y tempusdominus-bootstrap-4 ya cargados

(function () {
  function setWrapperSkipNextChange($wrapper) {
    if (!$wrapper || !$wrapper.length) return;
    $wrapper.data('df-skip-next-change', true);
    // fallback: limpiar aunque no dispare change
    setTimeout(() => {
      try {
        $wrapper.data('df-skip-next-change', false);
      } catch (e) {}
    }, 0);
  }

  function setMonthViewToYear($monthWrapper, year) {
    if (!year || !/^\d{4}$/.test(String(year))) return;
    const view = moment({ year: parseInt(year, 10), month: 0, day: 1 });
    if ($monthWrapper && $monthWrapper.length) {
      setWrapperSkipNextChange($monthWrapper);
      $monthWrapper.datetimepicker('viewDate', view);
    }
  }

  /**
   * initTempusFilters
   * @param {Object} cfg
   *  - form: selector del <form>
   *  - yearWrapper, monthWrapper, dayWrapper: selectores de los wrappers
   *  - yearInput, monthHiddenInput, monthDisplayInput, dayInput: selectores de inputs
   *  - initialYear: string opcional (ej. request('year'))
   *  - openGraceMs: ms para ignorar cambio por apertura (default 300)
   *  - autoSubmit: bool (default true) enviar form al cerrar cada picker si hubo cambio
   */
  function initTempusFilters(cfg) {
    const {
      form,
      yearWrapper,
      monthWrapper,
      dayWrapper,
      yearInput,
      monthHiddenInput,
      monthDisplayInput,
      dayInput,
      initialYear,
      openGraceMs = 300,
      autoSubmit = true,
    } = cfg;

    const $form = $(form);
    const $yearW = $(yearWrapper);
    const $monthW = $(monthWrapper);
    const $dayW = $(dayWrapper);

    const $year = $(yearInput);
    const $monthHidden = $(monthHiddenInput);
    const $monthDisplay = $(monthDisplayInput);
    const $day = $(dayInput);

    // Evitar doble init si ya se inicializó
    if ($yearW.data('df-initialized') || $monthW.data('df-initialized') || $dayW.data('df-initialized')) {
      return;
    }

    let settingYear = false;
    let settingMonth = false;
    let settingDay = false;

    // ===== YEAR =====
    if ($yearW.length) {
      $yearW.datetimepicker({
        format: 'YYYY',
        viewMode: 'years',
        useCurrent: false,
        keepOpen: false
      });

      const initYear = (initialYear || $yearW.data('initial-year') || $year.val() || '').toString();
      if (initYear) {
        settingYear = true;
        $yearW.datetimepicker('date', moment(initYear, 'YYYY'));
        settingYear = false;
      }

      let openingUntilTs = 0;

      $yearW
        .on('show.datetimepicker', function () {
          $(this).data('dirty', false);
          openingUntilTs = Date.now() + openGraceMs;
        })
        .on('change.datetimepicker', function (e) {
          if (settingYear) return;
          if (Date.now() < openingUntilTs) return;

          if (e.date) {
            const yearVal = e.date.year().toString();
            $year.val(yearVal);

            // Solo limpiar MONTH si no hay uno seleccionado
            if (!$monthHidden.val()) {
              settingMonth = true;
              $monthHidden.val('');
              if ($monthDisplay.length) $monthDisplay.val('');
              if ($monthW.length) $monthW.datetimepicker('clear');
              settingMonth = false;
            }

            // Limpiar siempre DAY
            settingDay = true;
            $day.val('');
            if ($dayW.length) $dayW.datetimepicker('clear');
            settingDay = false;

            setMonthViewToYear($monthW, yearVal);
          } else {
            $year.val('');
          }

          $(this).data('dirty', true);
        })
        .on('hide.datetimepicker', function () {
          if (autoSubmit && $(this).data('dirty')) {
            $form.submit();
            $(this).data('dirty', false);
          }
        });

      // Entrada manual de año (sin submit inmediato)
      $year.off('input blur').on('input blur', function () {
        const y = this.value.trim();
        if (/^\d{4}$/.test(y)) setMonthViewToYear($monthW, y);
      });

      $yearW.data('df-initialized', true);
    }

    // ===== MONTH =====
    if ($monthW.length) {
      $monthW.datetimepicker({
        format: 'MMM',
        viewMode: 'months',
        useCurrent: false,
        keepOpen: false
      });

      const mmHidden = ($monthHidden.val() || '').toString().padStart(2, '0');
      const baseYear = ($year.val() ? parseInt($year.val(), 10) : moment().year());

      if (mmHidden && mmHidden !== '00') {
        settingMonth = true;
        const m = moment({ year: baseYear, month: parseInt(mmHidden, 10) - 1, day: 1 });
        $monthW.datetimepicker('date', m);
        settingMonth = false;
      } else {
        if ($monthDisplay.length) $monthDisplay.val('');
        const y = $year.val();
        if (y) setMonthViewToYear($monthW, y);
      }

      $monthW.on('show.datetimepicker', function () {
        $(this).data('dirty', false);
        const y = $year.val() || moment().year();
        setMonthViewToYear($monthW, y);
      });

      $monthW.on('change.datetimepicker', function (e) {
        if ($(this).data('df-skip-next-change')) {
          $(this).data('df-skip-next-change', false);
          return;
        }
        if (settingMonth) return;

        if (e.date) {
          const monthVal = e.date.format('MM'); // 01..12
          $monthHidden.val(monthVal);

          // Sincroniza año, protegido
          const y = e.date.year().toString();
          settingYear = true;
          $year.val(y);
          if ($yearW.length) $yearW.datetimepicker('date', moment(y, 'YYYY'));
          settingYear = false;

          // Limpia el día
          settingDay = true;
          $day.val('');
          if ($dayW.length) $dayW.datetimepicker('clear');
          settingDay = false;
        } else {
          $monthHidden.val('');
        }

        $(this).data('dirty', true);
      });

      $monthW.on('hide.datetimepicker', function () {
        if (autoSubmit && $(this).data('dirty')) {
          $form.submit();
          $(this).data('dirty', false);
        }
      });

      // Re-proyectar mes cuando cambia YEAR
      if ($yearW.length) {
        $yearW.on('change.datetimepicker', function (e) {
          const selYear = e.date ? e.date.year() : ($year.val() ? parseInt($year.val(), 10) : baseYear);
          const currentMM = $monthHidden.val();
          if (currentMM) {
            settingMonth = true;
            const newDate = moment({ year: selYear, month: parseInt(currentMM, 10) - 1, day: 1 });
            $monthW.datetimepicker('date', newDate);
            settingMonth = false;
          } else {
            setMonthViewToYear($monthW, selYear);
          }
        });
      }

      $monthW.data('df-initialized', true);
    }

    // ===== DAY =====
    if ($dayW.length) {
      $dayW.datetimepicker({
        format: 'YYYY-MM-DD',
        viewMode: 'days',
        useCurrent: false,
        keepOpen: false,
      });

      const initDay = $day.val();
      if (initDay) {
        settingDay = true;
        $dayW.datetimepicker('date', moment(initDay, 'YYYY-MM-DD'));
        settingDay = false;
      } else {
        const y = $year.val();
        const mm = ($monthHidden.val() || '').toString().padStart(2, '0');
        if (y && mm && mm !== '00') {
          const view = moment({ year: parseInt(y, 10), month: parseInt(mm, 10) - 1, day: 1 });
          $dayW.datetimepicker('viewDate', view);
        }
      }

      $dayW.on('show.datetimepicker', function () {
        $(this).data('dirty', false);
        if (!$day.val()) {
          const y = $year.val();
          const mm = $monthHidden.val();
          if (y && mm && mm !== '00') {
            const view = moment({ year: parseInt(y, 10), month: parseInt(mm, 10) - 1, day: 1 });
            $dayW.datetimepicker('viewDate', view);
          } else {
            $dayW.datetimepicker('viewDate', moment());
          }
        }
      });

      $dayW.on('change.datetimepicker', function (e) {
        if (settingDay) return;

        if (e.date) {
          const d = e.date.clone();
          $day.val(d.format('YYYY-MM-DD'));

          // YEAR
          settingYear = true;
          const y = d.format('YYYY');
          $year.val(y);
          if ($yearW.length) $yearW.datetimepicker('date', moment(y, 'YYYY'));
          settingYear = false;

          // MONTH
          settingMonth = true;
          const mm = d.format('MM');
          $monthHidden.val(mm);
          if ($monthW.length) $monthW.datetimepicker('date', d.clone().startOf('month'));
          settingMonth = false;
        } else {
          $day.val('');
        }

        $(this).data('dirty', true);
      });

      $dayW.on('hide.datetimepicker', function () {
        if (autoSubmit && $(this).data('dirty')) {
          $form.submit();
          $(this).data('dirty', false);
        }
      });

      // Mover solo la vista del day-picker cuando cambia Year/Month y NO hay day seleccionado
      if ($yearW.length) {
        $yearW.on('change.datetimepicker', function (e) {
          if (!$day.val()) {
            const selYear = e.date ? e.date.year() : ($year.val() || moment().year());
            const mm = ($monthHidden.val() || '01').toString().padStart(2, '0');
            const view = moment({ year: parseInt(selYear, 10), month: parseInt(mm, 10) - 1, day: 1 });
            $dayW.datetimepicker('viewDate', view);
          }
        });
      }
      if ($monthW.length) {
        $monthW.on('change.datetimepicker', function (e) {
          if (!$day.val()) {
            const y = $year.val() ? parseInt($year.val(), 10) : moment().year();
            const mm = $monthHidden.val() || (e.date ? e.date.format('MM') : '01');
            const view = moment({ year: y, month: parseInt(mm, 10) - 1, day: 1 });
            $dayW.datetimepicker('viewDate', view);
          }
        });
      }

      $dayW.data('df-initialized', true);
    }
  }

  // Exponer global para llamarlo desde cada vista
  window.initTempusFilters = initTempusFilters;
})();
