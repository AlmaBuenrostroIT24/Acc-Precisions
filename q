[33m8a8011b[m[33m ([m[1;36mHEAD -> [m[1;32mfaisummary[m[33m)[m Sin cambios
[33mf279da0[m[33m ([m[1;31morigin/faisummary[m[33m)[m ultimas modificaciones para ajustar permisos para shipping
[33me845cc1[m se modifico el archivo services inpectionsummary para add tables al resultado final y que no se rompar la informacion de una hoja a otra
[33mbfd4087[m Se add los filtros del Tab Summary para la tabla
[33m055074c[m se ajuste de acuerdo a la revision con leo 08/29/25
[33m45bfd04[m se agrego el report pass & no pass en el pdf
[33m225c09d[m ajuste tabla de packet report
[33m37b7b3e[m Se ADD los campos de date y sampling plan para traer la informacion en el tab completen FAi summary
[33m65c7446[m Se save el campo inspection_endate al al completar la inspectiob
[33m64315fb[m Se ajusto los datos de la tabla report
[33m1467711[m Se fix el save de operation fai_total y ipi_total
[33mbe90886[m Se cambio el select por input qty_insp y se ajuste al max de wo_qty
[33m79010d1[m Se add la validacion para guardar los valos de sampling_check y sampling si se modifica
[33m21ebbd1[m Se modifico en js, para que acepte cantidad de operaciones por revision
[33ma6dff45[m Casi se completo generacion del pdf
[33m9489fd6[m Se puso certificado y sxe ajusto para que se vizualice el numero de pagina
[33m8453ae3[m Open pdf as modal en la misma pagina
[33m5c1fc82[m Merge master into schedule: resolve conflicts
[33m23059ad[m[33m ([m[1;31morigin/master[m[33m)[m Merge branch 'schedule'
[33m73e46c9[m Resumen de Validación y Confirmación de Estado 'sent' en caso de que el campo wo_qty optimizando el codigo
[33ma72fe89[m Resumen de Validación y Confirmación de Estado 'sent' en caso de que el campo wo_qty con ajustes de error
[33md85c9ab[m Resumen de Validación y Confirmación de Estado 'sent' en caso de que el campo wo_qty se encuentre vacio o sea igual a 0
[33m528dd9e[m se add los campos total_fai, total_ipi, sampling, status-inspection y ademas se ajusto el metodo de .btn-add-kit para copiar los campos co y cust_po a nueva fila add
[33me6468c9[m Cambio de status_inspection, de acuerdo si se ha add alguna o no, o en caso se ha eliminado
[33m0967252[m Se add la alerta cuando las inspection llega al 100%
[33m90d27dc[m Se add la condicion en la tabla summary que solo muestre el mes en corriente
[33mf21f2bd[m Se add la table de summary en tab de todos los registros de inspections que se realizaran
[33m3087350[m Se add los metodos byOrderOperator, byOrderStation para que se traiga de la base de datos, las stations y los operators
[33m9c48137[m Se modifiposicion addrowbtn, se reasignaron las variable globales, hasta este momento esta funcionando al 80%
[33m8e2de5e[m Merge branch 'schedule'
[33m239f52e[m Se modifico el error del import
[33m607040a[m Modificaciones de  QAfai
[33m9fd968e[m se puso los permisos para ver las vista de QAfai
[33m8162c21[m se mejoro la consulta, para que no vean cual workid sea null, ya que esos no llevara inspeccion
[33ma4f863c[m se add 2 tablas, una para registar ordenes con nuevo FAI, y otra cuando ya tiene al menos uno, para dar seguimiento
[33maa5754e[m Merge branch 'schedule'
[33ma642133[m Se hicieron commit todos los comentarios
[33md4ffeda[m Merge branch 'schedule'
[33m5cc5a1b[m Se fix acomodo el color de prioridad
[33m926c1ca[m Se fix el orden de las ordenes tomando en primer prioridad y despuues fecha de vencimiento
[33m2f226d3[m Se fix el problema de agregar filas
[33meb9c5e0[m Se actulizo la consulta en la vistasTV del schedule de hearst y yarnell
[33m5b5b6ee[m Se reutilizo el modal para los botones de delete y priority en tab general schedule
[33mc950d79[m Se update el indice para que aparezcan los botones de kit
[33m8c0c8fb[m Se add swalfire alert
[33m6c64d0b[m Se add la eliminacion de las ordenes y se mejoro el alerta del tab verde
[33mce4afa0[m Se add la validacion de FAI & IPI de acuerdo a las validaciones sean visibles y el borrar un elemento
[33m618b53c[m Merge branch 'schedule'
[33ma3d61a0[m Cambio del icon accprecision
[33m24c40f2[m Se corrigio el filtro de customer en el tab ordersYarnell
[33mf239ab7[m fix el la fecha end mach para el orden de la tabla en tab orders yarnell
[33m3d27d45[m fix el orden de la tabla en tab completed orders
[33mbff28f9[m fix table statistics in tab orders statistics
[33m8b45afd[m se add la grafica de On Time VS Late Delivers
[33mfc05e07[m se add la grafica de next 8 weeks
[33m5f65d94[m logo arreglado
[33mb0df948[m se add que cuando sea onhold se pueda cambiar el due_date
[33mc93b529[m se ajusto el orden de la tbla en las vistas de tv yarnell, hearst
[33m0cd24a9[m arreglo del style en table Orders Yarnell
[33m4bb416f[m Update de asc para la table
[33md712cb4[m  se realizo el tab scheduleHearst, para el manejo de status, se add la vallidacion al eviar a sent
[33m022842f[m se modifico en orderScheduleImportService los 5 dias para mach date
[33m828469c[m se arreglo el problem al momento de crearse la tabla
[33m644703f[m cual quier cambio
[33m9803f74[m Se add en el blade welcome el codigo de login para cuando se haga logout, se vaya a esta pagina
[33ma794a0f[m Middleware de rutas
[33md7aea17[m Ultimas modificaciones del Scheduile Hearst, pero hace falta el select del status
[33m4bd4870[m Finalizacion de tablas tab scheduleHearst
[33m8af5556[m se fix cuando se selecciona status, que se actualizaba la tabla y se iba al inicio(arriba)
[33m205cf4c[m se fix cuando se selecciona location, que se actualizaba la tabla y se iba al inicio(arriba)
[33m8fee3c5[m se add las tablas de ready to deliver & orders in deburring en tab ScheduleHearst
[33mfa9cf64[m se add el tab schedulehearst
[33m9842f88[m Se add location floor y yarnell al usuario QAdmin, en el controller
[33m95506ce[m Se add el status ready, para calcular los days, el metodo calcular diasInterno en el controller
[33me59056f[m Se corrigio que cuando WOQTY se borre aparezca cero en lugar de vacio
[33mdbd1650[m V2 Fix error cuando se borra el WOQTY
[33mb061cb9[m Fix error cuando se borra el WOQTY
[33mc0cb50f[m V2 Se add el alert a crear una orden de kit
[33m411afe6[m Se add el alert a crear una orden de kit
[33m7dbed45[m Fix status 'Ajuste style colum WOQTY
[33mf6eb9a7[m add report inspection
[33m2dd0d01[m add campos woqty, qty to check
[33m712799c[m se add la migracion qa_samplingplans
[33md04882e[m se add fila, se guarda, edita
[33m7ac92be[m se add el metodo para agregar no. operations
[33mcc06932[m se crearon las views para esta categoria
[33m68979ea[m creacion de la tbala qa_faisummary, para la migracion
[33m1e521b4[m ajustando cosas del master
[33me900606[m ajustes en ramas
[33mb47257a[m Fix status 'pendient' in filter status
[33mb7ed367[m Cambios style scheduley
[33m67a63c2[m Cambios en el estilo de la tabla
[33ma23316d[m Fix parte del guardado de nuevas ordenes, respecto error del select
[33md6f99e0[m detecta la ultima actualizacion de una orden para actualizar vistas en PCS
[33mdb299aa[m se add nuevos status
[33m2a92071[m se add el card de weekly orders
[33m02d4230[m 2. en el tab orders stastics se realizo pendig order this week
[33me5739a1[m en el tab orders stastics se realizo pendig order this week
[33m8581034[m se add al cargar la pagina, establecer semana actual si no hay valor
[33m3be7cbf[m se add metodoaaplyRowlate, para que me detecte el color del status de acuerdo al cambio de la fecha
[33m1c21609[m color rojo para cuando days<0, en todas las vistas del schedule
[33m8f3f617[m se mejoro el diseño de la tabla en completed orders
[33m7a6d060[m se mejoro el filtro de completed_orders
[33m03e15a0[m se add el btn para regresar la orden y el metodo returnPreviousStatus
[33m6de6c12[m alert cuando status es sent, y en caso de cancelar, que el status anterior regrese
[33m0d6dab3[m Comentarios en el js de hearst & Yarnell
[33mcd2db9b[m Optimizacion de codigo para las vistas hearst & Yarnell, se creo un solo js
[33m459e91f[m Funcionamiento de las vistas Yarnell & Hearst, al cambiar machining date
[33me6b6fe7[m Centraliza las configuraciones por ruta en un solo objeto (tableConfigs).
[33m27bb080[m Registro quien realizo cambio de machining_date
[33mcff05a6[m dias restantes y alert label se actualiza automaticamente
[33mcb1c633[m se optimizo el metodo de boton report y outsourse. y al hacer click outsource y sea igual a 1, deja cambiar la fecha mach.date
[33m901001e[m cambio en la interfaz scheduley, scheduleh, para que detecte el cambio de location
[33m5d9b5a8[m Interfaz Orders Yarnell
[33m37d9815[m add la condicion de cambio de estatus deburring, shipping, si es yarnell que se cambie a hearst, add la etiqueta de yarnell y con un alerta de confirmacion
[33me851369[m add last location, en la interfaz Completed Orders
[33m5a97bc7[m add last location, para que aparezca etiqueta si la orden viene de Yarnell
[33md5dfec6[m algun ajuste
[33m6b1041d[m vizualizacion de ordenes por clientes in orders statistics
[33md5d220e[m add btn excel, pdf, print OrdersStatics
[33m3ef7f7c[m se ajusto filters and order charts
[33m6269909[m se add mas palabras para add btn
[33me07b68e[m Fix lentitud del input wo_qty
[33m86cce27[m reparando errores
[33m1f7f0e8[m Merge branch 'schedule'
[33mf630b99[m Se agregro con la rama Schedule
[33m41e8865[m error en el master con appserviceorderschedule
[33m55226f1[m moodificacion de notes en la vista completed orders
[33m6df07ba[m add wo_qty a la vista completed orders
[33m95cf366[m update views yarnell, hearst con el campo wo_qty
[33ma5e8419[m terminacion de la new fila
[33m78eaa2b[m update indice notes e indice duedate
[33m12cc36d[m fix error default_value in col notes
[33m662eb85[m fix error default_value in col notes
[33m9bfc61d[m add el campo wo_qty
[33mc77e3dd[m acomodo del logo
[33m3584ff0[m modificacion del logout que inice en login
[33m3347e00[m fix preloader
[33md68df1c[m ultimos ajustes in interfaz ordersstatistics
[33mb33d289[m impresion de graficas
[33m12adadb[m creacion de 2 graficas in OrderStatistics
[33m9cfba6d[m delete fila cuando status is sent, scheduley&h
[33m0bda786[m js actualizado para la interfaz scheduleh
[33m162cce8[m excel, pdf, print, copy, orderStatistcs
[33ma482bb8[m Fix tables, select general schedule
[33m6afc7c4[m Filtros de Completed Orders personalizados
[33m0d249d9[m Filtros de Completed Orders
[33maa428ee[m Funcionamiento de la plantilla anterior
[33mb419d4f[m trabajando con el menu y acceso de usuarios
[33m36ceebc[m efecto de pantalla cuando se actualiza
[33m1ad049b[m Merge branch 'users'
[33m9becf41[m[33m ([m[1;31morigin/users[m[33m, [m[1;32musers[m[33m)[m modaledit create user
[33m5756927[m Creacion de vistas user
[33m96bc41c[m[33m ([m[1;31morigin/RolesPermissions[m[33m, [m[1;32mRolesPermissions[m[33m)[m Plantilla terminada rols&permission
[33maf3ac2a[m create the route, controller and views user
[33mdd4fa01[m Create menu principal
[33m4c68151[m Create master
[33m75fc9b5[m mejorando el add new fila kit
[33m14544ad[m creando fila de acuerdo a kit
[33m1f3bc91[m ajuste de la tabla principal
[33mde3eb24[m cambio se diseno de selects
[33m7249ecf[m se add el campo wo_qty
