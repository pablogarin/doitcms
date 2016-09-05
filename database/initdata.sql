INSERT INTO dom(id,type,domId,className,closeTag,parentDom,domOrder) VALUES
(-1,'html','','',true,-1,0.0),
(1,'head','','',true,-1,0.1),
(2,'body','','',true,-1,0.2),
(3,'meta','','',false,1,0.1),
(4,'meta','','',false,1,0.2),
(5,'meta','','',false,1,0.3),
(6,'title','','',true,1,0.4),
(7,'link','','',false,1,0.5),
(8,'div','','container',true,2,0.1),
(9,'nav','','navbar navbar-default',true,8,0.1),
(10,'div','main-content','container-fluid',true,8,0.2),
(11,'script','','',true,2,0.99),
(12,'script','','',true,2,0.99),
(13,'div','','row',true,10,0.1),
(14,'h1','','col-md-12 text-center',true,13,0.1),
(15,'div','','container-fluid',true,9,0.1),
(16,'div','','navbar-header',true,15,0.1),
(17,'div','navbar','navbar-collapse collapse',true,15,0.1),
(18,'button','','navbar-toggle collapsed',true,16,0.1),
(19,'span','','sr-only',true,18,0.1),
(20,'span','','icon-bar top-bar',true,18,0.2),
(21,'span','','icon-bar middle-bar',true,18,0.3),
(22,'span','','icon-bar bottom-bar',true,18,0.4),
(23,'a','','navbar-brand',true,16,0.1),
(24,'ul','','nav navbar-nav',true,17,0.1),
(25,'li','','active',true,24,0.1),
(26,'a','','',true,25,0.1),
(27,'link','','',false,1,0.6),
(28,'link','','',false,1,0.7),
(29,'script','','',true,2,0.99);
-- GO
INSERT INTO atribute(id,name,value) VALUES
(1,'lang','es'),
(2,'charset','utf-8'),
(3,'http-equiv','X-UA-Compatible'),
(4,'content','IE=edge'),
(5,'name','viewport'),
(6,'content','width=device-width, initial-scale=1'),
(7,'href','/css/bootstrap.min.css'),
(8,'rel','stylesheet'),
(9,'src','https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'),
(10,'src','/js/bootstrap.min.js'),
(11,'type','button'),
(12,'data-toggle','collapse'),
(13,'data-target','#navbar'),
(14,'aria-expanded','false'),
(15,'aria-controls','navbar'),
(16,'href','#'),
(17,'href','{{url}}'),
(18,'href','/css/font-awesome.min.css'),
(19,'href','/css/styles.css'),
(20,'rel','stylesheet'),
(21,'rel','stylesheet'),
(22,'src','/js/script.js');
-- GO
INSERT INTO dom_atribute(idDom,idAtribute) VALUES
(3,2),
(4,3),
(4,4),
(5,5),
(5,6),
(7,7),
(7,8),
(11,9),
(12,10),
(18,11),
(18,12),
(18,13),
(18,14),
(18,15),
(23,16),
(26,17),
(27,18),
(27,20),
(28,19),
(28,21),
(29,22);
-- GO
INSERT INTO template(id, name, description) VALUES
(-1, 'layout', 'HTML Base del sitio.'),
(1, 'home', 'Home del sitio.');
-- GO
INSERT INTO template_dom(idTemplate, idDom) VALUES
(-1,-1),
(-1,1),
(-1,2),
(-1,3),
(-1,4),
(-1,5),
(-1,6),
(-1,7),
(-1,8),
(-1,9),
(-1,10),
(-1,11),
(-1,12),
(-1,15),
(-1,16),
(-1,17),
(-1,18),
(-1,19),
(-1,20),
(-1,21),
(-1,22),
(-1,23),
(-1,24),
(-1,25),
(-1,26),
(-1,27),
(-1,28),
(-1,29),
(1,13),
(1,14);
-- GO
INSERT INTO section(id,name,parentSection,sectionOrder,active) VALUES
(-1,'Home',-1,0.00,true);
-- GO
INSERT INTO text(id,name,body) VALUES
(1,'Titulo','Titulo de la Pagina'),
(2,'Saludos','Hola Mundo!');
-- GO
INSERT INTO content(id,name,url,tableName,keyName,keyValue) VALUES
(1,'Menu Home','/','section','id','-1'),
(2,'Titulo Sitio','/','text','id','1'),
(3,'Texto Bienvenida','home','text','id','2');
-- GO
INSERT INTO content_dom(idContent,idDom) VALUES
(1,26),
(2,6),
(3,14);
