INSERT INTO dom(id,type,domId,className,closeTag,parentDom,domOrder) VALUES
(-1,'html','','',true,-1,0.0),
(1,'head','','',true,-1,0.1),
(2,'body','','',true,-1,0.2),
(3,'meta','','',false,1,0.1),
(4,'meta','','',false,1,0.2),
(5,'meta','','',false,1,0.3),
(6,'title','','',true,1,0.4),
(7,'link','','',false,1,0.5),
(8,'h1','','',true,2,0.1),
(9,'script','','',true,2,0.99),
(10,'script','','',true,2,0.99);
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
(10,'src','/js/bootstrap.min.js');
INSERT INTO dom_atribute(idDom,idAtribute) VALUES
(3,2),
(4,3),
(4,4),
(5,5),
(5,6),
(7,7),
(7,8),
(9,9),
(10,10);
