jQuery(function($){
  // Helpers
  function showFeedback(msg, type){
    $('#ss-member-feedback').text(msg).css('color', type==='error'?'#c0392b':'#27ae60');
  }
  function closeModal(){ $('#ss-member-form-modal').hide().empty(); }
  function openModal(html){ $('#ss-member-form-modal').html(html).show(); }

  // Cargar listado
  function loadMembers(search){
    showFeedback('');
    $.post(ajaxurl, {action:'sabbathschool_member_list', search:search||'', _ajax_nonce:ssMembers.nonce}, function(resp){
      if(resp.success){
        renderMembers(resp.data.results);
      } else {
        showFeedback(resp.data.message||'Error','error');
      }
    });
  }

  // Render cards
  function renderMembers(members){
    let html = '';
    if(!members.length){ html = '<div>No hay miembros.</div>'; }
    members.forEach(function(m){
      html += `<div class="ss-member-card">
        <div><b>${m.first_name} ${m.last_name}</b> <small>(${m.role})</small></div>
        <div>${m.identification} | ${m.email||''}</div>
        <div>${m.mobile||m.phone||''}</div>
        <div class="ss-card-actions">
          <button class="ss-btn ss-btn-primary" data-edit="${m.id}">Editar</button>
          <button class="ss-btn ss-btn-cancel" data-deactivate="${m.id}">Baja</button>
        </div>
      </div>`;
    });
    $('#ss-members-list').html(html);
  }

  // Modal formulario alta/edición
  function loadMemberForm(id){
    $.post(ajaxurl, {action:'sabbathschool_member_form', id:id||''}, function(html){
      openModal(html);
    });
  }

  // Buscar
  $('#ss-member-search-form').on('submit', function(e){
    e.preventDefault();
    loadMembers($('#ss-member-search').val());
  });

  // Nuevo
  $('#ss-member-add').on('click', function(){
    loadMemberForm();
  });

  // Editar/Baja delegados
  $('#ss-members-list').on('click', '[data-edit]', function(){
    loadMemberForm($(this).data('edit'));
  });
  $('#ss-members-list').on('click', '[data-deactivate]', function(){
    if(confirm('¿Dar de baja este miembro?')){
      $.post(ajaxurl, {action:'sabbathschool_member_deactivate', id:$(this).data('deactivate'), _ajax_nonce:ssMembers.nonce}, function(resp){
        if(resp.success){ showFeedback('Miembro dado de baja.'); loadMembers(); closeModal(); }
        else showFeedback(resp.data.message||'Error','error');
      });
    }
  });

  // Modal cancelar
  $(document).on('click','#ss-member-cancel', function(){ closeModal(); });
  $(document).on('click','.ss-modal', function(e){ if(e.target===this) closeModal(); });

  // Guardar miembro (alta/edición)
  $(document).on('submit','#ss-member-form',function(e){
    e.preventDefault();
    var data = $(this).serializeArray();
    data.push({name:'action', value: $('#ss-member-form [name=id]').val() ? 'sabbathschool_member_update' : 'sabbathschool_member_create'});
    data.push({name:'_ajax_nonce', value: ssMembers.nonce});
    $.post(ajaxurl, data, function(resp){
      if(resp.success){
        showFeedback('Guardado correctamente.');
        loadMembers();
        closeModal();
      } else {
        showFeedback(resp.data.message||'Error','error');
      }
    });
  });

  // Inicial
  loadMembers();
});
