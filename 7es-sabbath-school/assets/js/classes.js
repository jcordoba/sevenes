/* classes.js */
jQuery(function($){
  // Helpers
  function showFeedback(msg, type){
    $('#ss-class-feedback').text(msg).css('color', type==='error'?'#c0392b':'#27ae60');
  }
  function closeModal(){
    $('#ss-class-form-modal').hide().empty();
    // Mobile: mostrar listado al cerrar
    if(window.matchMedia('(max-width: 700px)').matches){
      $('.ss-classes-list, #ss-class-search-form').show();
      $('body').removeClass('ss-modal-open');
    }
  }
  function openModal(html){
    $('#ss-class-form-modal').html(html).show();
    // Mobile: ocultar listado y usar pantalla completa
    if(window.matchMedia('(max-width: 700px)').matches){
      $('.ss-classes-list, #ss-class-search-form').hide();
      $('body').addClass('ss-modal-open');
    }
  }

  // Cargar listado
  function loadClasses(search){
    showFeedback('');
    $.post(ajaxurl, {action:'sabbathschool_class_list', search:search||'', _ajax_nonce:ssClasses.nonce}, function(resp){
      if(resp.success){
        renderClasses(resp.data.results);
      } else {
        showFeedback(resp.data.message||'Error','error');
      }
    });
  }

  // Render cards
  function renderClasses(classes){
    let html = '';
    if(!classes.length){ html = '<div>No hay clases/unidades.</div>'; }
    classes.forEach(function(c){
      html += `<div class="ss-class-card">
        <div><b>${c.name}</b></div>
        <div>Maestro: ${c.teacher_id} | Asistente: ${c.assistant_id||'-'}</div>
        <div>${c.description||''}</div>
        <div class="ss-card-actions">
          <button class="ss-btn ss-btn-primary" data-edit="${c.id}">Editar</button>
          <button class="ss-btn ss-btn-cancel" data-deactivate="${c.id}">Baja</button>
        </div>
      </div>`;
    });
    $('#ss-classes-list').html(html);
  }

  // Modal formulario alta/edición
  function loadClassForm(id){
    $.post(ajaxurl, {action:'sabbathschool_class_form', id:id||''}, function(html){
      openModal(html);
    });
  }

  // Buscar
  $('#ss-class-search-form').on('submit', function(e){
    e.preventDefault();
    loadClasses($('#ss-class-search').val());
  });

  // Nueva
  $('#ss-class-add').on('click', function(){
    loadClassForm();
  });

  // Editar/Baja delegados
  $('#ss-classes-list').on('click', '[data-edit]', function(){
    loadClassForm($(this).data('edit'));
  });
  $('#ss-classes-list').on('click', '[data-deactivate]', function(){
    if(confirm('¿Dar de baja esta clase/unidad?')){
      $.post(ajaxurl, {action:'sabbathschool_class_deactivate', id:$(this).data('deactivate'), _ajax_nonce:ssClasses.nonce}, function(resp){
        if(resp.success){ showFeedback('Clase dada de baja.'); loadClasses(); closeModal(); }
        else showFeedback(resp.data.message||'Error','error');
      });
    }
  });

  // Modal cancelar
  $(document).on('click','#ss-class-cancel', function(){ closeModal(); });
  $(document).on('click','.ss-modal', function(e){ if(e.target===this) closeModal(); });

  // Guardar clase (alta/edición)
  $(document).on('submit','#ss-class-form',function(e){
    e.preventDefault();
    var data = $(this).serializeArray();
    data.push({name:'action', value: $('#ss-class-form [name=id]').val() ? 'sabbathschool_class_update' : 'sabbathschool_class_create'});
    data.push({name:'_ajax_nonce', value: ssClasses.nonce});
    $.post(ajaxurl, data, function(resp){
      if(resp.success){
        showFeedback('Guardado correctamente.');
        loadClasses();
        closeModal();
      } else {
        showFeedback(resp.data.message||'Error','error');
      }
    });
  });

  // Inicial
  loadClasses();
});
