/*global jQuery, dotclear, CodeMirror */
'use strict';

dotclear.ready(() => {
  // DOM ready and content loaded

  dotclear.mergeDeep(dotclear, dotclear.getData('sysinfo'));

  const dotclearAjax = (method, args, fn, msg = '') => {
    dotclear.jsonServicesGet(
      method,
      (payload) => {
        fn(payload.html);
      },
      args,
      (error) => {
        if (dotclear.debug) console.log(error);
        window.alert(msg);
      },
    );
  };

  const getStaticCacheFilename = (url, fn) => dotclearAjax('getStaticCacheName', { url }, fn, '');

  const loadStaticCacheDirs = (dir, fn) => dotclearAjax('getStaticCacheDir', { root: dir }, fn, '');

  const loadStaticCacheList = (dir, fn) => dotclearAjax('getStaticCacheList', { root: dir }, fn, '');

  const loadServerFile = (filename, type, fn) => {
    switch (type) {
      case 'tpl':
        dotclearAjax('getCompiledTemplate', { file: filename }, fn, dotclear.msg.tpl_not_found);
        break;
      case 'sc':
        dotclearAjax('getStaticCacheFile', { file: filename }, fn, dotclear.msg.sc_not_found);
        break;
    }
  };

  const viewSource = (prefix, filename, content, mode, title = '') => {
    let cm_editor; // Codemirror instance
    const real_title = title === '' ? '' : `${title} - `;
    const src = `<div class="${prefix}_view"><h1>${real_title}${filename}</h1><textarea id="${prefix}_source">${JSON.parse(window.atob(content))}</textarea></div>`;
    jQuery.magnificPopup.open({
      items: {
        src,
        type: 'inline',
      },
      callbacks: {
        open: () => {
          if (!dotclear.colorsyntax) {
            return;
          }
          // Popup opened, format textarea with codemirror
          const options = {
            mode: mode || 'text/html',
            tabMode: 'indent',
            lineWrapping: 'true',
            lineNumbers: 'true',
            matchBrackets: 'true',
            autoCloseBrackets: 'true',
            readOnly: 'true',
          };
          if (dotclear.colorsyntax_theme !== '') {
            options.theme = dotclear.colorsyntax_theme;
          }
          const textarea = document.getElementById(`${prefix}_source`);
          cm_editor = CodeMirror.fromTextArea(textarea, options);
        },
        close: () => {
          if (cm_editor != null) {
            // Remove Codemirror instance
            cm_editor.toTextArea();
          }
        },
      },
    });
  };

  // Compiled template preview
  const tpl_compiled_list = document.querySelectorAll('a.tpl_compiled');
  for (const tpl_compiled of tpl_compiled_list) {
    tpl_compiled.addEventListener('click', (event) => {
      event.preventDefault();
      const template_file = event.target.textContent;
      // Open template file content in a modal iframe
      if (template_file !== undefined) {
        loadServerFile(template_file, 'tpl', (content) => {
          viewSource('tpl_compiled', template_file, content, 'php', event.target.title);
        });
      }
    });
  }

  if (document.getElementById('scform')) {
    // Static cache dir expand (load 2nd level subdirs via Ajax)
    const show = (event) => {
      event.preventDefault();
      const cache_file = event.target.getAttribute('data-file');
      // Open static cache file content in a modal iframe
      if (cache_file !== undefined) {
        loadServerFile(cache_file, 'sc', (content) => {
          viewSource('sc_compiled', event.target.textContent, content);
        });
      }
    };

    const sc_dirs = document.querySelectorAll('a.sc_dir');
    for (const sc_dir of sc_dirs) {
      sc_dir.addEventListener('click', (event) => {
        event.preventDefault();
        const main_dir = event.currentTarget.textContent;
        loadStaticCacheDirs(main_dir, (dirs) => {
          // Insert list and remove previous raw
          const line = event.target.parentNode.parentNode;
          line.after(dotclear.htmlToNode(dirs));
          line.remove();
          // Static cache subdir expand (load 3rd level subdirs and cache file list via Ajax)
          const sc_subdirs = document.querySelectorAll('a.sc_subdir');
          for (const sc_subdir of sc_subdirs) {
            sc_subdir.addEventListener('click', (event_sub) => {
              event_sub.preventDefault();
              const sub_dir = event_sub.currentTarget.textContent;
              loadStaticCacheList(`${main_dir}/${sub_dir}`, (list) => {
                // Insert list and remove previous raw
                for (const sc_compiled of document.querySelectorAll('a.sc_compiled')) {
                  sc_compiled.removeEventListener('click', show);
                }
                const raw = event_sub.target.parentNode.parentNode;
                raw.after(dotclear.htmlToNode(list));
                raw.remove();
                dotclear.condSubmit('#scform td input[type=checkbox]', '#scform #delscaction');
                // Static cache file preview
                for (const sc_compiled of document.querySelectorAll('a.sc_compiled')) {
                  sc_compiled.addEventListener('click', show);
                }
              });
            });
          }
        });
      });
    }
  }

  // Autosubmit on checklist change
  document.getElementById('checklist')?.addEventListener('change', (event) => {
    event.currentTarget?.form.submit();
  });

  // Static cache calculator
  if (document.getElementById('scform')) {
    document.getElementById('getscaction')?.addEventListener('click', (event) => {
      const show = (event) => {
        event.preventDefault();
        const res = event.currentTarget.textContent;
        const cache_file = `${res.slice(0, 2)}/${res.slice(2, 4)}/${res.slice(4, 6)}/${res}`;
        const cache_dir = event.currentTarget.getAttribute('data-dir');
        loadServerFile(`${cache_dir}/${cache_file}`, 'sc', (content) => {
          viewSource('sc_compiled', res, content);
        });
      };

      event.preventDefault();
      const result = document.getElementById('sccalc_res');
      const preview = document.getElementById('sccalc_preview');
      result.textContent = '';
      preview.textContent = '';
      preview.removeEventListener('click', show);

      const url = document.getElementById('sccalc_url')?.value;
      if (url !== undefined && url !== '') {
        getStaticCacheFilename(url, (res) => {
          const text = `${String.fromCharCode(160) + res.slice(0, 2)} / ${res.slice(2, 4)} / ${res.slice(4, 6)} / `;
          result.textContent = text;
          preview.textContent = res;
          preview.focus();
          preview.addEventListener('click', show);
        });
      }
    });
  }

  // Checkboxes helpers

  // Template cache files
  if (document.getElementById('tplform')) {
    for (const item of document.querySelectorAll('#tplform .checkboxes-helpers')) {
      dotclear.checkboxesHelpers(item, undefined, '#tplform td input[type=checkbox]:not(:disabled)', '#tplform #deltplaction');
    }
    dotclear.enableShiftClick('#tplform td input[type=checkbox]');
    dotclear.condSubmit('#tplform td input[type=checkbox]', '#tplform #deltplaction');
    document
      .querySelector('form input[type=submit][name=deltplaction]')
      ?.addEventListener('click', (event) => dotclear.confirm(dotclear.msg.confirm_del_tpl, event));
  }

  // Versions in DB
  if (document.getElementById('verform')) {
    for (const item of document.querySelectorAll('#verform .checkboxes-helpers')) {
      dotclear.checkboxesHelpers(item, undefined, '#verform td input[type=checkbox]:not(:disabled)', '#verform #delveraction');
    }
    dotclear.enableShiftClick('#verform td input[type=checkbox]');
    dotclear.condSubmit('#verform td input[type=checkbox]', '#verform #delveraction');
    document
      .querySelector('form input[type=submit][name=delveraction]')
      ?.addEventListener('click', (event) => dotclear.confirm(dotclear.msg.confirm_del_ver, event));
  }

  // Static cache files
  if (document.getElementById('scform')) {
    for (const item of document.querySelectorAll('#scform .checkboxes-helpers')) {
      dotclear.checkboxesHelpers(item, undefined, '#scform td input[type=checkbox]:not(:disabled)', '#scform #delscaction');
    }
    dotclear.enableShiftClick('#scform td input[type=checkbox]');
    dotclear.condSubmit('#scform td input[type=checkbox]', '#scform #delscaction');
    document
      .querySelector('form input[type=submit][name=delscaction]')
      ?.addEventListener('click', (event) => dotclear.confirm(dotclear.msg.confirm_del_sc, event));
  }

  // Expand/Contract all (details)
  document.getElementById('expand-all')?.addEventListener('click', (event) => {
    event.preventDefault();
    const items = document.querySelectorAll('#content details');
    if (event.currentTarget.getAttribute('open')) {
      // Close all
      for (const item of items) item.removeAttribute('open');
    } else {
      // Open all
      for (const item of items) item.setAttribute('open', 'open');
    }
  });

  const li = document.getElementById('sys_battery');
  if (li) {
    // Battery level (if API exists)
    if (navigator.getBattery) {
      navigator.getBattery().then((battery) => {
        const level = battery.level * 100;
        const [str] = li.getElementsByTagName('strong'); // Get first strong in li element
        if (str) {
          str.innerText = `${level}%`;
        }
      });
    } else {
      li.style.display = 'none';
    }
  }

  const attic = document.getElementById('attic');
  if (attic) {
    dotclearAjax('getAtticVersions', {}, (html) => {
      attic.outerHTML = html;
    });
  }

  // Table sorting enabler
  const enableTableSort = (tableId, offset = 0, semver = -1, numeric = -1) => {
    const table = document.getElementById(tableId);
    if (!table) {
      return;
    }
    table.classList.add('js-sortable');
    const headers = table.querySelectorAll('th');
    // Give keyboard access to header to activate column sort
    for (const header of headers) {
      if (header.innerText) header.setAttribute('tabindex', '0');
    }
    const tableBody = table.querySelector('tbody');
    const rows = tableBody.querySelectorAll('tr');
    // Track sort directions
    const directions = Array.from(headers).map(() => {});
    // Sort system
    const sortColumn = (indexData, indexHead) => {
      // Get the current direction
      const direction = directions[indexHead] || 'asc';

      // A factor based on the direction
      const multiplier = direction === 'asc' ? 1 : -1;

      // Clone the rows
      const newRows = Array.from(rows);

      function comparePartials(a, b) {
        if (a === b) {
          return 0;
        }
        const splitA = a.split('.');
        const splitB = b.split('.');
        const length = Math.max(splitA.length, splitB.length);
        for (let i = 0; i < length; i++) {
          // flip
          if (
            Number.parseInt(splitA[i]) > Number.parseInt(splitB[i]) ||
            (splitA[i] === splitB[i] && Number.isNaN(splitB[i + 1]))
          ) {
            return 1 * multiplier;
          }
          // don't flip
          if (
            Number.parseInt(splitA[i]) < Number.parseInt(splitB[i]) ||
            (splitA[i] === splitB[i] && Number.isNaN(splitA[i + 1]))
          ) {
            return -1 * multiplier;
          }
        }
      }

      // Sort rows by the content of cells
      newRows.sort((rowA, rowB) => {
        // Get the content of cells
        let cellA = rowA.querySelectorAll('td')[indexData].innerHTML;
        let cellB = rowB.querySelectorAll('td')[indexData].innerHTML;

        if (semver === indexHead) return comparePartials(cellA, cellB);
        if (numeric === indexHead) {
          cellA = Number.parseInt(rowA.querySelectorAll('td')[indexData].innerText);
          cellB = Number.parseInt(rowB.querySelectorAll('td')[indexData].innerText);
          switch (true) {
            case cellA > cellB:
              return 1 * multiplier;
            case cellA < cellB:
              return -1 * multiplier;
            case cellA === cellB:
              return 0;
          }
        }
        return cellA.localeCompare(cellB) * multiplier;
      });

      // Remove old rows
      for (const row of rows) tableBody.removeChild(row);

      // Append new row
      for (const row of newRows) tableBody.appendChild(row);

      // Remove old headers class
      for (const header of headers) {
        header.classList.remove('sorted-asc');
        header.classList.remove('sorted-desc');
      }

      // Set new header class
      headers[indexHead].classList.add(`sorted-${direction}`);

      // Reverse the direction
      directions[indexHead] = direction === 'asc' ? 'desc' : 'asc';
    };
    if (headers.length)
      for (let index = headers.length - 1; index >= 0; index--) {
        headers[index].addEventListener('click', () => sortColumn(index + offset, index));
        headers[index].addEventListener('keypress', (event) => {
          if (event.key === 'Enter') {
            event.target.click();
            event.preventDefault();
          }
        });
      }
  };

  // Add sorting mecanism to some tables
  for (const table of [
    'antispams',
    'autoloads',
    'constants',
    'dotclear-config',
    'dotclear-release',
    'permissions',
    'restmethods',
    'tplpaths',
    'urls',
    'statuses_blog',
    'statuses_user',
    'statuses_post',
    'statuses_comment',
    'posttypes',
  ])
    enableTableSort(table);
  // Sorting for some specific tables (one column is numeric)
  enableTableSort('thumbnails', 0, -1, 1);
  enableTableSort('statuses_blog', 0, -1, 2);
  enableTableSort('statuses_user', 0, -1, 2);
  enableTableSort('statuses_post', 0, -1, 2);
  enableTableSort('statuses_comment', 0, -1, 2);
  enableTableSort('exceptions', 0, -1, 2);
});
